<?php

/**
 * @copyright Ilch 2
 * @package ilch
 */

namespace Modules\Gamelist\Mappers;

use Modules\Gamelist\Models\Category as CategoryModel;

class Category extends \Ilch\Mapper
{
    /**
     * Gets categorys.
     *
     * @param array $where
     * @return CategoryModel[]|[]
     */
    public function getCategories(array $where = []): array
    {
        $categoryArray = $this->db()->select('*')
            ->from('gamelist_cats')
            ->where($where)
            ->execute()
            ->fetchRows();

        if (empty($categoryArray)) {
            return [];
        }

        $categorys = [];
        foreach ($categoryArray as $categoryRow) {
            $categoryModel = new CategoryModel();
            $categoryModel->setId($categoryRow['id']);
            $categoryModel->setTitle($categoryRow['title']);

            $categorys[] = $categoryModel;
        }

        return $categorys;
    }

    /**
     * Returns user model found by the id or false if none found.
     *
     * @param int $id
     * @return false|CategoryModel
     */
    public function getCategoryById(int $id)
    {
        $cats = $this->getCategories(['id' => $id]);
        return reset($cats);
    }

    /**
     * Returns first non-empty category.
     *
     * @return null|CategoryModel
     */
    public function getCategoryMinId(): ?CategoryModel
    {
        $categoryRow = $this->db()->select('*')
            ->fields(['c.id', 'c.title', 'games' => 'f.catid'])
            ->from(['c' => 'gamelist_cats'])
            ->join(['f' => 'gamelist'], 'c.id = f.catid')
            ->order(['c.id' => 'ASC'])
            ->limit('1')
            ->execute()
            ->fetchAssoc();

        if (empty($categoryRow)) {
            return null;
        }

        $categoryModel = new CategoryModel();
        $categoryModel->setId($categoryRow['id']);

        return $categoryModel;
    }

    /**
     * Inserts or updates category model.
     *
     * @param CategoryModel $category
     */
    public function save(CategoryModel $category)
    {
        if ($category->getId()) {
            $this->db()->update('gamelist_cats')
                ->values(['title' => $category->getTitle()])
                ->where(['id' => $category->getId()])
                ->execute();
        } else {
            $this->db()->insert('gamelist_cats')
                ->values(['title' => $category->getTitle()])
                ->execute();
        }
    }

    /**
     * Deletes category with given id.
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->db()->delete('gamelist_cats')
            ->where(['id' => $id])
            ->execute();
    }
}
