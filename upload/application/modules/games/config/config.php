<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Games\Config;

class Config extends \Ilch\Config\Install
{
    public $config = [
        'key' => 'games',
        'version' => '1.0',
        'icon_small' => 'fa-gamepad',
        'author' => 'Veldscholten, Kevin',
        'link' => 'http://ilch.de',
        'languages' => [
            'de_DE' => [
                'name' => 'Spieleliste',
                'description' => 'Hier kannst du die Spieleliste verwalten.',
            ],
            'en_EN' => [
                'name' => 'Game list',
                'description' => 'Here you can manage the game list.',
            ],
        ],
        'ilchCore' => '2.1.15',
        'phpVersion' => '5.6'
    ];

    public function install()
    {
        $this->db()->queryMulti($this->getInstallSql());
    }

    public function uninstall()
    {
        $this->db()->queryMulti('DROP TABLE `[prefix]_games`;
            DROP TABLE `[prefix]_games_entrants`;');
        $this->db()->queryMulti("DELETE FROM `[prefix]_user_menu_settings_links` WHERE `key` = 'games/index/settings';");
    }

    public function getInstallSql()
    {
        $installSql =
            'CREATE TABLE IF NOT EXISTS `[prefix]_games` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(100) NOT NULL,
                `image` VARCHAR(255) NULL DEFAULT NULL,
                `show` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1;

            CREATE TABLE IF NOT EXISTS `[prefix]_games_entrants` (
                `game_id` INT(11) NOT NULL,
                `user_id` INT(11) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            INSERT INTO `[prefix]_user_menu_settings_links` (`key`, `locale`, `description`, `name`) VALUES
                ("games/index/settings", "de_DE", "Hier kannst du deine Spielliste bearbeiten.", "Spieleauswahl"),
                ("games/index/settings", "en_EN", "Here you can manage your game list.", "Games selection");';

        return $installSql;
    }

    public function getUpdate($installedVersion)
    {

    }
}
