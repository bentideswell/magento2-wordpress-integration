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
     * @var \Zend_Db_Expr|string
     */
    private $permalinkSqlColumn = null;

    /**
     * @param \FishPig\WordPress\App\ResourceConnection $resourceConnection 
     */
    public function __construct(
       \FishPig\WordPress\App\ResourceConnection $resourceConnection,
       \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
       \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->postTypeRepository = $postTypeRepository;
        $this->taxonomyRepository = $taxonomyRepository;
    }
    
    /**
     * @param  string $pathInfo
     * @return int|false
     */
    public function getPostIdByPathInfo($pathInfo)
    {
        $cacheKey = strtolower(rtrim($pathInfo));
        
        if (isset($this->pathInfoIdMap[$cacheKey])) {
            return $this->pathInfoIdMap[$cacheKey];
        }
        
        $this->pathInfoIdMap[$cacheKey] = false;

        $fields = $this->getPermalinkSqlFields();
            
        foreach ($this->postTypeRepository->getAll() as $postType) {
            if (!$postType->isPublic()) {
                continue;   
            }

            if (($filters = $this->getPostTypeFilters($postType, $pathInfo)) === false) {
                continue;
            }

            $select = $this->getConnection()->select()
                ->from(
                    [
                        'main_table' => $this->resourceConnection->getTable('posts')
                    ], 
                    [
                        'id' => 'ID', 
                        'permalink' => $this->getPermalinkSqlColumn()
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

            if ($routes = $this->getConnection()->fetchPairs($select)) {
                
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
    public function completePostSlug($slug, $postId, $postType)
    {
        if (!preg_match_all('/(\%[a-z0-9_-]{1,}\%)/U', $slug, $matches)) {
            return $slug;
        }

        $matchedTokens = $matches[0];

        foreach ($matchedTokens as $mtoken) {
            if ($mtoken === '%postnames%') {
                $slug = str_replace($mtoken, $postType->getHierarchicalPostName($postId), $slug);
            } elseif ($taxonomy = $this->taxonomyManager->getTaxonomy(trim($mtoken, '%'))) {
                $termData = $this->getParentTermsByPostId([$postId], $taxonomy->getTaxonomyType(), false);

                foreach ($termData as $key => $term) {
                    if ((int)$term['object_id'] === (int)$postId) {
                        $slug = str_replace($mtoken, $taxonomy->getUriById($term['term_id'], false), $slug);

                        break;
                    }
                }
            }
        }

        return urldecode($slug);
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

        $fields = $this->getPermalinkSqlFields();
        $tokens = $postType->getExplodedPermalinkStructure();
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
                                $pathInfo = rtrim(substr($pathInfo, strrpos(rtrim($pathInfo, '/'), '/')), '/');
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
     * @return string|\Zend_Db_Expr
     */
    public function getPermalinkSqlColumn()
    {
        if ($this->permalinkSqlColumn === null) {
            $postTypes  = $this->postTypeRepository->getAll();
            $sqlColumns = [];
            $fields     = $this->getPermalinkSqlFields();
    
            foreach ($postTypes as $postType) {
                $tokens = $postType->getExplodedPermalinkStructure();
                $sqlFields = [];
    
                foreach ($tokens as $token) {
                    if (substr($token, 0, 1) === '%' && isset($fields[trim($token, '%')])) {
                        $sqlFields[] = $fields[trim($token, '%')];
                    } else {
                        $sqlFields[] = "'" . $token . "'";
                    }
                }
    
                if (count($sqlFields) > 0) {
                    $sqlColumns[$postType->getPostType()] = ' WHEN `post_type` = \'' . $postType->getPostType() . '\' THEN (CONCAT(' . implode(', ', $sqlFields) . '))';
                }
            }
    
            $this->permalinkSqlColumn = count($sqlColumns) > 0
                ? new \Zend_Db_Expr('(' . sprintf('CASE %s END', implode('', $sqlColumns)) . ')')
                : '';
        }
        
        return $this->permalinkSqlColumn;
    }

    /**
     * @return
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
