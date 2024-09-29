<?php

// Версия приложения
define('VERSION', '1.0.0.0');

// Определяем корневую директорию
$dir_root        = realpath(__DIR__ . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$dir_private     = dirname($dir_root) . DIRECTORY_SEPARATOR;

// Директории для системы и конфигурации
$dir_core        = $dir_private . 'system' . DIRECTORY_SEPARATOR;
$dir_config      = $dir_private . 'config' . DIRECTORY_SEPARATOR;

// Имя приложения
$application     = 'catalog';
$dir_application = $dir_root . $application . DIRECTORY_SEPARATOR;

// Путь к конфигурационному файлу
$path_config = $dir_config . strtolower($application) . '.php';

// Проверка наличия конфигурационного файла
if (!is_file($path_config) || filesize($path_config) < 10) {
    header('Location: error.html');
    exit();
}

// Подключение основного приложения
require_once($dir_core . 'application.php');

// Создание экземпляра приложения
$core = new \Myshop\System\Application();

// Установка путей для хранения, расширений и изображений
$core->dir_storage   = $dir_private . 'storage' . DIRECTORY_SEPARATOR;
$core->dir_extension = $dir_root . 'extension' . DIRECTORY_SEPARATOR;
$core->dir_image     = $dir_root . 'image' . DIRECTORY_SEPARATOR;

// Загрузка конфигурации из файла
$config = \Myshop\System\Config\Factory::fromFile($path_config);

// Запуск приложения
$core->start($dir_application, $application, $config);

