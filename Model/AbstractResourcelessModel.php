<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

abstract class AbstractResourcelessModel extends AbstractModel
{
    /**
     * @return false
     */
    public function getResource()
    {
        return false;
    }

    /**
     * @return false
     */
    public function getResourceCollection()
    {
        return false;
    }

    /**
     * @return false
     */
    public function getCollection()
    {
        return false;
    }

    /**
     * @return $this
     */
    public function save()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        return $this;
    }
}
