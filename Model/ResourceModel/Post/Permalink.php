<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Post;

use FishPig\WordPress\Model\PostType;

class Permalink
{
    /**
     * @var array
     */
    private $pathInfoIdMap = [];

    /**
     * @param \FishPig\WordPress\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Model\ResourceModel\HierarchicalUrlGenerator $hierarchicalUrlGenerator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->postTypeRepository = $postTypeRepository;
        $this->taxonomyRepository = $taxonomyRepository;
        $this->hierarchicalUrlGenerator = $hierarchicalUrlGenerator;
    }
    
    /**
     * @param  string $pathInfo
     * @return int|false
     */
    public function getPostIdByPathInfo($pathInfo)
    {
        // This fixes issues with a starting slash
        // But also fixes issues with encoded characters like %CC%
        // getting confused as tokens (eg. %category%) later on
        $pathInfo = urldecode(ltrim($pathInfo, '/'));
        
        $cacheKey = strtolower(rtrim($pathInfo));

        if (isset($this->pathInfoIdMap[$cacheKey])) {
            return $this->pathInfoIdMap[$cacheKey];
        }
        
        $this->pathInfoIdMap[$cacheKey] = false;
        
        // No point in matching an empty pathInfo
        if ($pathInfo === '') {
            return $this->pathInfoIdMap[$cacheKey];
        }
        
        $fields = $this->getPermalinkSqlFields();
        $db = $this->getConnection();

        foreach ($this->postTypeRepository->getAll() as $postType) {
            if (!$postType->isPublic()) {
                continue;
            }

            $routes = false;

            if ($postType->isHierarchical()) {
                $routes = $this->hierarchicalUrlGenerator->generateRoutes(
                    $db->fetchAll(
                        $db->select()->from(
                            $this->resourceConnection->getTable('posts'),
                            [
                                'id' => 'ID',
                                'parent' => 'post_parent',
                                'url_key' => 'post_name'
                            ]
                        )->where(
                            'post_name IN (?)',
                            explode('/', $pathInfo)
                        )->where(
                            'post_type = ?',
                            $postType->getPostType()
                        )->where(
                            'post_status IN (?)',
                            ['publish', 'protected', 'private']
                        )
                    )
                );
            } else {
                if (($filters = $this->getPostTypeFilters($postType, $pathInfo)) === false) {
                    continue;
                }

                $select = $this->getConnection()->select()
                    ->from(
                        ['main_table' => $this->resourceConnection->getTable('posts')],
                        [
                            'id' => 'ID',
                            'permalink' => $this->getPermalinkSqlColumn($postType->getPostType())
                        ]
                    )->where(
                        'post_type = ?',
                        $postType->getPostType()
                    )->where(
                        'post_status IN (?)',
                        ['publish', 'protected', 'private']
                    )->limit(
                        1
                    );
    
                foreach ($filters as $field => $value) {
                    if (isset($fields[$field])) {
                        $select->where($fields[$field] . ' = ?', urlencode($value));
                    }
                }

                $routes = $this->getConnection()->fetchPairs($select);
            }
            
            if ($routes) {
                foreach ($routes as $id => $permalink) {
                    if (rtrim($pathInfo, '/') === rtrim($this->completePostSlug($permalink, $id, $postType), '/')) {
                        return $this->pathInfoIdMap[$cacheKey] = $id;
                    }
                }
            }
        }

        return $this->pathInfoIdMap[$cacheKey];
    }

    /**
     * @param  string   $slug
     * @param  int      $postId
     * @param  PostType $postType
     * @return string
     */
    public function completePostSlug(string $slug, int $postId, PostType $postType)
    {
        if (!$slug || !preg_match_all('/(\%[a-z0-9_-]{1,}\%)/U', $slug, $matches)) {
            return $slug;
        }

        $matchedTokens = $matches[0];
        
        foreach ($matchedTokens as $mtoken) {
            if ($mtoken === '%postnames%') {
                $slug = str_replace($mtoken, $postType->getHierarchicalPostName($postId), $slug);
            } else {
                try {
                    $taxonomy = $this->taxonomyRepository->get(trim($mtoken, '%'));
                    if ($termId = $this->getParentTermId($postId, $taxonomy->getTaxonomy())) {
                        $slug = str_replace($mtoken, $taxonomy->getUriById($termId, false), $slug);
                    }
                    // Change
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // We can ignore this exception
                }
            }
        }

        return urldecode($slug);
    }

    /**
     * @param  int    $postId
     * @param  string $taxonomy = 'category'
     * @return int
     */
    public function getParentTermId(int $postId, $taxonomy = 'category'): int
    {
        $select = $this->getConnection()->select()
            ->distinct()
            ->from(['_relationship' => $this->resourceConnection->getTable('wordpress_term_relationship')], null)
            ->where('object_id = ?', $postId)
            ->order('_term.term_id ASC')
            ->limit(1);

        $select->join(
            ['_taxonomy' => $this->resourceConnection->getTable('wordpress_term_taxonomy')],
            $this->getConnection()->quoteInto(
                "_taxonomy.term_taxonomy_id = _relationship.term_taxonomy_id AND _taxonomy.taxonomy= ?",
                $taxonomy
            ),
            null
        );

        $select->join(
            ['_term' => $this->resourceConnection->getTable('wordpress_term')],
            "`_term`.`term_id` = `_taxonomy`.`term_id`",
            ['term_id']
        );

        return (int)$this->getConnection()->fetchOne($select);
    }

    /**
     * @param  PostType $postType
     * @param  string $pathInfo
     * @return array|false
     */
    private function getPostTypeFilters(PostType $postType, string $pathInfo)
    {
        if ($postType->permalinkHasTrainingSlash()) {
            $pathInfo = rtrim($pathInfo, '/') . '/';
        }

        // If permalink structure has more slashes than pathInfo, cannot match
        if (substr_count(rtrim($postType->getPermalinkStructure(), '/'), '/') > substr_count($pathInfo, '/')) {
            return false;
        }

        $fields = $this->getPermalinkSqlFields();
        $tokens = $this->getExplodedPermalinkStructure($postType);
        $filters = [];
        $lastToken = $tokens[count($tokens)-1];

        // Allow for trailing static strings (eg. .html)
        if (substr($lastToken, 0, 1) !== '%') {
            if (substr($pathInfo, -strlen($lastToken)) !== $lastToken) {
                return false;
            }

            $pathInfo = substr($pathInfo, 0, -strlen($lastToken));

            array_pop($tokens);
        }

        for ($i = 0; $i <= 1; $i++) {
            if ($i === 1) {
                $pathInfo = implode('/', array_reverse(explode('/', $pathInfo)));
                $tokens = array_reverse($tokens);
            }

            foreach ($tokens as $key => $token) {
                if (substr($token, 0, 1) === '%') {
                    if (!isset($fields[trim($token, '%')])) {
                        $taxonomyToken = trim($token, '%');
                        
                        if ($this->taxonomyRepository->exists($taxonomyToken)) {
                            $taxonomy = $this->taxonomyRepository->get($taxonomyToken);
                            $endsWithPostname = isset($tokens[$key+1]) && $tokens[$key+1] === '/'
                                && isset($tokens[$key+2]) && $tokens[$key+2] === '%postname%'
                                && !isset($tokens[$key+3]);

                            if ($endsWithPostname) {
                                $pos = strrpos(rtrim($pathInfo, '/'), '/');
                                $pathInfo = $pos !== false ? rtrim(substr($pathInfo, $pos), '/') : '';
                                continue;
                            }
                        }

                        break;
                    }

                    if (isset($tokens[$key+1]) && substr($tokens[$key+1], 0, 1) !== '%') {
                        $filters[trim($token, '%')] = substr($pathInfo, 0, strpos($pathInfo, $tokens[$key+1]));
                        $pathInfo = substr($pathInfo, strpos($pathInfo, $tokens[$key+1]));
                    } elseif (!isset($tokens[$key+1])) {
                        $filters[trim($token, '%')] = $pathInfo;
                        $pathInfo = '';
                    } else {
                        return false;
                    }

                    // Check if field requires number
                    if (in_array($token, ['%year%', '%day%', '%monthnum%'])) {
                        if ((int)$filters[trim($token, '%')] === 0) {
                            return false;
                        }
                    }
                } elseif (substr($pathInfo, 0, strlen($token)) === $token) {
                    $pathInfo = substr($pathInfo, strlen($token));
                } else {
                    return false;
                }

                unset($tokens[$key]);
            }
        }

        return count($filters) > 0 ? $filters : false;
    }
            
    /**
     * @return array
     */
    private function getPermalinkSqlFields(): array
    {
        return [
            'year' => 'SUBSTRING(post_date_gmt, 1, 4)',
            'monthnum' => 'SUBSTRING(post_date_gmt, 6, 2)',
            'day' => 'SUBSTRING(post_date_gmt, 9, 2)',
            'hour' => 'SUBSTRING(post_date_gmt, 12, 2)',
            'minute' => 'SUBSTRING(post_date_gmt, 15, 2)',
            'second' => 'SUBSTRING(post_date_gmt, 18, 2)',
            'post_id' => 'ID',
            'postname' => 'post_name',
            'author' => 'post_author',
        ];
    }

    /**
     * @param  array $requiredPostTypes = null
     * @return string|\Zend_Db_Expr
     */
    public function getPermalinkSqlColumn($requiredPostTypes = null)
    {
        if ($requiredPostTypes) {
            $requiredPostTypes = (array)$requiredPostTypes;
        }

        $postTypes = $this->postTypeRepository->getAll();
        $sqlColumns = [];
        $fields = $this->getPermalinkSqlFields();
        $db = $this->resourceConnection->getConnection();
        
        foreach ($postTypes as $postType) {
            if ($requiredPostTypes !== null && !in_array($postType->getPostType(), $requiredPostTypes)) {
                continue;
            }

            $tokens = $this->getExplodedPermalinkStructure($postType);
            $sqlFields = [];

            foreach ($tokens as $token) {
                if (substr($token, 0, 1) === '%' && isset($fields[trim($token, '%')])) {
                    $sqlFields[] = $fields[trim($token, '%')];
                } else {
                    $sqlFields[] = $db->quoteInto('?', $token);
                }
            }

            if (count($sqlFields) > 0) {
                $sqlColumns[$postType->getPostType()] = $db->quoteInto(
                    'WHEN post_type = ?  THEN CONCAT(' . implode(', ', $sqlFields) . ')',
                    $postType->getPostType()
                );
            }
        }

        return count($sqlColumns) > 0
            ? new \Zend_Db_Expr('(' . sprintf('CASE %s END', implode('', $sqlColumns)) . ')')
            : '';
    }

    /**
     * Retrieve the permalink structure in array format
     *
     * @return false|array
     */
    private function getExplodedPermalinkStructure(PostType $postType)
    {
        $structure = $postType->getPermalinkStructure();
        $parts = preg_split("/(\/|-)/", $structure, -1, PREG_SPLIT_DELIM_CAPTURE);
        $structure = [];

        foreach ($parts as $part) {
            if ($result = preg_split("/(%[a-zA-Z0-9_]{1,}%)/", $part, -1, PREG_SPLIT_DELIM_CAPTURE)) {
                $results = array_filter(array_unique($result));

                foreach ($results as $result) {
                    array_push($structure, $result);
                }
            } else {
                $structure[] = $part;
            }
        }

        return $structure;
    }

    /**
     * @return
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
