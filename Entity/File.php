<?php

namespace App\Bundle\FileBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Bundle\UserBundle\Entity\TimestampableTrait;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 *@Vich\Uploadable
 */
class File
{
    use TimestampableTrait;

    protected $id;

    protected $path;

    protected $createdAt;

    protected $updatedAt;

    protected $mineType;

     /**
     * @var \SplFileInfo
     *
     * @Vich\UploadableField(mapping="default", fileNameProperty="path")
     */
    protected $file;

    /**
     * @var array
     */
    protected $owner;

    public function getId()
    {
        return $this->id;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function setMineType($mineType)
    {
        $this->mineType = $mineType;

        return $this;
    }

    public function getMineType()
    {
        return $this->mineType;
    }

      /**
     * {@inheritdoc}
     */
    public function getFile(): ?\SplFileInfo
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function setFile(?\SplFileInfo $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }
}
