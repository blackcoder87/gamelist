<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Gamelist\Controllers\Admin;

use Modules\Gamelist\Mappers\Games as GamesMapper;
use Modules\Gamelist\Models\Games as GamesModel;
use Modules\Gamelist\Mappers\Category as CategoryMapper;
use Ilch\Validation;

class Index extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'manage',
                'active' => false,
                'icon' => 'fa-solid fa-table-list',
                'url' => $this->getLayout()->getUrl(['controller' => 'index', 'action' => 'index']),
                [
                    'name' => 'add',
                    'active' => false,
                    'icon' => 'fa-solid fa-circle-plus',
                    'url' => $this->getLayout()->getUrl(['controller' => 'index', 'action' => 'treat'])
                ]
            ],
            [
                'name' => 'menuCats',
                'active' => false,
                'icon' => 'fa-solid fa-table-list',
                'url' => $this->getLayout()->getUrl(['controller' => 'cats', 'action' => 'index'])
            ]
        ];

        if ($this->getRequest()->getActionName() == 'treat') {
            $items[0][0]['active'] = true;
        } else {
            $items[0]['active'] = true;
        }

        $this->getLayout()->addMenu
        (
            'menuGames',
            $items
        );
    }

    public function indexAction()
    {
        $gamesMapper = new GamesMapper();
        $categoryMapper = new CategoryMapper();

        $this->getLayout()->getAdminHmenu()
            ->add($this->getTranslator()->trans('menuGames'), ['action' => 'index'])
            ->add($this->getTranslator()->trans('manage'), ['action' => 'index']);

        if ($this->getRequest()->getPost('check_entries')) {
            if ($this->getRequest()->getPost('action') == 'delete') {
                foreach ($this->getRequest()->getPost('check_entries') as $id) {
                    $gamesMapper->delete($id);
                }
            }
        }

        $this->getView()->set('categoryMapper', $categoryMapper);
        $this->getView()->set('entries', $gamesMapper->getEntries());
    }

    public function treatAction()
    {
        $gamesMapper = new GamesMapper();
        $categoryMapper = new CategoryMapper();

        if ($this->getRequest()->getParam('id')) {
            $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuGames'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('edit'), ['action' => 'treat']);

            $this->getView()->set('entry', $gamesMapper->getEntryById($this->getRequest()->getParam('id')));
        } else {
            $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuGames'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('add'), ['action' => 'treat']);
        }

        if ($this->getRequest()->isPost()) {
            // Add BASE_URL if image starts with application to get a complete URL for validation
            $image = trim($this->getRequest()->getPost('image'));
            if (!empty($image)) {
                if (substr($image, 0, 11) == 'application') {
                    $image = BASE_URL.'/'.$image;
                }
            }

            $post = [
                'catid' => $this->getRequest()->getPost('catid'),
                'title' => $this->getRequest()->getPost('title'),
                'videourl' => $this->getRequest()->getPost('videourl'),
                'image' => $image
            ];

            $newvideoUrl = str_replace("https://www.youtube.com/watch?v=","", $post['videourl']);

            $validation = Validation::create($post, [
                'title'  => 'required',
                'image' => 'required|url'
            ]);

            $post['image'] = trim($this->getRequest()->getPost('image'));

            if ($validation->isValid()) {
                $model = new GamesModel();
                if ($this->getRequest()->getParam('id')) {
                    $model->setId($this->getRequest()->getParam('id'));
                }
                $model->setTitle($post['title'])
                      ->setCatId($post['catid'])
                      ->setVideourl($newvideoUrl)
                      ->setImage($post['image']);
                $gamesMapper->save($model);

                $this->redirect()
                    ->withMessage('saveSuccess')
                    ->to(['action' => 'index']);
            }

            $this->addMessage($validation->getErrorBag()->getErrorMessages(), 'danger', true);
            $this->redirect()
                ->withInput()
                ->withErrors($validation->getErrorBag())
                ->to(['action' => 'treat']);
        }
        $this->getView()->set('cats', $categoryMapper->getCategories());
    }

    public function updateAction()
    {
        if ($this->getRequest()->isSecure()) {
            $gamesMapper = new GamesMapper();
            $gamesMapper->update($this->getRequest()->getParam('id'));

            $this->addMessage('saveSuccess');
        }

        $this->redirect(['action' => 'index']);
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $gamesMapper = new GamesMapper();
            $gamesMapper->delete($this->getRequest()->getParam('id'));

            $this->addMessage('deleteSuccess');
        }

        $this->redirect(['action' => 'index']);
    }
}
