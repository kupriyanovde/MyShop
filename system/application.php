<?php

namespace Myshop\System;

use Myshop\System\Config\Config;
use Myshop\System\Config\Factory;
use Myshop\System\Engine\Action;
use Myshop\System\Engine\Cache;
use Myshop\System\Engine\DB;
use Myshop\System\Engine\Document;
use Myshop\System\Engine\Event;
use Myshop\System\Engine\Loader;
use Myshop\System\Engine\Log;
use Myshop\System\Engine\Registry;
use Myshop\System\Engine\Request;
use Myshop\System\Engine\Response;
use Myshop\System\Engine\Session;
use Myshop\System\Engine\Language;
use Myshop\System\Engine\Template;
use Myshop\System\Engine\Url;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'startup.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'helper' . DIRECTORY_SEPARATOR . 'general.php');

if (!defined('NAMESPACE_SEPARATOR')) {
    define('NAMESPACE_SEPARATOR', '\\');
}

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');

class Application
{
    public string $dir_storage;
    public string $dir_extension;
    public string $dir_image;
    private string $_dir_system;
    private Autoloader $_autoloader;
    private Config $_config;
    private Registry $_registry;
    private Event $_event;
    private Engine\Factory $_factory;
    private Loader $_loader;
    private Log $_log;
    private Log $_log_shop;
    private Request $_request;
    private Response $_response;
    private Cache $_cache;
    private Template $_template;
    private Language $_language;
    private Session $_session;
    private Url $_url;
    private Document $_document;
    private DB $_db;

    public function __construct() {
        $this->_dir_system = realpath(__DIR__ . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->initializePaths();
        $this->initializeAutoloader();
    }

    private function initializePaths(): void {
        $dir_root = dirname($this->_dir_system) . DIRECTORY_SEPARATOR;
        $this->dir_storage = $dir_root . 'storage' . DIRECTORY_SEPARATOR;
        $this->dir_extension = $dir_root . 'extension' . DIRECTORY_SEPARATOR;
        $this->dir_image = $dir_root . 'image' . DIRECTORY_SEPARATOR;
    }

    private function initializeAutoloader(): void {
        $this->_autoloader = new Autoloader();
        $this->_autoloader->register(__NAMESPACE__, __DIR__ . DIRECTORY_SEPARATOR);
        $this->_autoloader->register(__NAMESPACE__ . NAMESPACE_SEPARATOR . 'Library', $this->_dir_system . 'library' . DIRECTORY_SEPARATOR);
        $this->_autoloader->register(__NAMESPACE__ . NAMESPACE_SEPARATOR . 'Helper', $this->_dir_system . 'helper' . DIRECTORY_SEPARATOR);
        $this->_autoloader->register(__NAMESPACE__ . NAMESPACE_SEPARATOR . 'Model', $this->_dir_system . 'system' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR);
        $this->_autoloader->register(__NAMESPACE__ . NAMESPACE_SEPARATOR . 'Repository', $this->_dir_system . 'system' . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR);
    }

    public function start(string $dir_application, string $application, array $config): void {
        $this->init($dir_application, $application, $config);
        $this->registerEvents();

        $route = $this->getRoute();
        $args = [];
        $error_action = new Action($this->_config['action_error']);

        $next_action = $this->startActions($this->_config['action_pre_action']->toArray(), $args, $error_action);
        if ($next_action) {
            $route = $next_action->getId();
        }

        $trigger = $route;
        $this->_event->trigger('controller/' . $trigger . '/before', [&$route, &$args]);

        $next_action = $next_action ?? new Action($route);
        $this->dispatch($next_action, $args, $error_action);

        $this->_event->trigger('controller/' . $trigger . '/after', [&$route, &$args, &$output]);

        $this->startActions($this->_config['action_post_action']->toArray(), $args, $error_action);
        $this->output();
    }

    private function getRoute(): string {
        $route = $this->_config['action_default'];
        if (isset($this->_request->get['route'])) {
            $route = str_replace(['|', '%7C'], '.', (string)$this->_request->get['route']);
        }
        return $route;
    }

    private function init(string $dir_application, string $application, array $config): void {
        $dir_application = realpath($dir_application . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->dir_storage = realpath($this->dir_storage . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->dir_extension = realpath($this->dir_extension . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->dir_image = realpath($this->dir_image . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        $this->_config = $this->loadConfig($application, $config);
        $this->initializePathsInConfig();
        date_default_timezone_set($this->_config['date_timezone']);
        $this->initializeLogging();
        $this->initializeComponents();
        $this->initializeLanguageAndTemplate();
    }

    private function loadConfig(string $application, array $config): Config {
        $path_config = $this->_dir_system . 'config' . DIRECTORY_SEPARATOR . 'default.php';
        $config = Factory::fromFile($path_config, true, false)->merge(new Config($config));
        $config['application_name'] = ucfirst($application);
        
        if (empty($config['application_namespace'])) {
            $config['application_namespace'] = explode(NAMESPACE_SEPARATOR, __NAMESPACE__)[0];
        }
        return rtrim($config['application_namespace'], NAMESPACE_SEPARATOR);
    }

    private function initializePathsInConfig(): void {
        $paths = [
            'path_root'        => $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR,
            'path_system'      => $this->_dir_system,
            'path_storage'     => $this->dir_storage,
            'path_backup'      => $this->dir_storage . 'backup' . DIRECTORY_SEPARATOR,
            'path_cache'       => $this->dir_storage . 'cache' . DIRECTORY_SEPARATOR,
            'path_download'    => $this->dir_storage . 'download' . DIRECTORY_SEPARATOR,
            'path_logs'        => $this->dir_storage . 'logs' . DIRECTORY_SEPARATOR,
            'path_marketplace' => $this->dir_storage . 'marketplace' . DIRECTORY_SEPARATOR,
            'path_session'     => $this->dir_storage . 'session' . DIRECTORY_SEPARATOR,
            'path_upload'      => $this->dir_storage . 'upload' . DIRECTORY_SEPARATOR,
            'path_image'       => $this->dir_image,
            'path_application' => $this->dir_application,
            'path_language'    => $this->dir_application . 'language' . DIRECTORY_SEPARATOR,
            'path_template'    => $this->dir_application . 'view' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR,
            'path_extension'   => $this->dir_extension
        ];
        $this->_config->merge(new Config($paths));
    }

    private function initializeLogging(): void {
        require_once(__DIR__ . '/vendor.php');
        load_vendor(__DIR__ . '/vendor/', $this->_autoloader);

        $this->_log = new Log($this->_config['path_logs'] . 'core.log');
        $this->registerErrorHandlers();

        $this->_log_shop = new Log($this->_config['path_logs'] . $this->_config['error_filename']);
    }

    private function initializeComponents(): void {
        $this->_registry = new Registry();
        $this->_request = new Request($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
        $this->_response = new Response();
        $this->_cache = new Cache($this->_config['cache_engine'], $this->_config['path_cache'], $this->_config['cache_expire']);
        $this->_template = new Template($this->_config['template_engine'], $this->_config['path_root'], $this->_config['path_cache']);
        $this->_language = new Language($this->_config['language_code']);
        $this->_url = new Url($this->_config['site_url']);
        $this->_document = new Document();

        // Инициализация базы данных
        $this->_db = new DB(
            $this->_config['db_engine'],
            $this->_config['db_driver'],
            $this->_config['db_hostname'],
            $this->_config['db_username'],
            $this->_config['db_password'],
            $this->_config['db_database'],
            $this->_config['db_port'],
            $this->_config['db_ssl_key'],
            $this->_config['db_ssl_cert'],
            $this->_config['db_ssl_ca']
        );

        // Настройка реестра
        $this->_registry->set('request', $this->_request);
        $this->_registry->set('response', $this->_response);
        $this->_registry->set('cache', $this->_cache);
        $this->_registry->set('template', $this->_template);
        $this->_registry->set('language', $this->_language);
        $this->_registry->set('url', $this->_url);
        $this->_registry->set('document', $this->_document);
        $this->_registry->set('db', $this->_db);

        // Инициализация событий и сессий
        $this->_event = new Event($this->_registry);
        $this->_factory = new Engine\Factory($this->_registry);
        $this->_loader = new Loader($this->_registry);
        $this->_session = new Session($this->_config['session_engine'], $this->_registry);

        // Регистрация событий и компонентов в реестре
        $this->_registry->set('event', $this->_event);
        $this->_registry->set('factory', $this->_factory);
        $this->_registry->set('load', $this->_loader);
        $this->_registry->set('session', $this->_session);

        // Загрузка языковых файлов и шаблонов
        $this->_template->addPath($this->_config['path_template']);
        $this->_language->addPath($this->_config['path_language']);
        $this->_language->load('default');
    }

    private function registerErrorHandlers(): void {
        $log = $this->_log;
        $display = $this->_config['error_display'];

        // Обработчик ошибок
        set_error_handler(function(int $code, string $message, string $file, int $line) use ($log, $display) {
            $error_type = match ($code) {
                E_NOTICE, E_USER_NOTICE => 'notice',
                E_WARNING, E_USER_WARNING => 'warning',
                E_ERROR, E_USER_ERROR => 'critical',
                default => 'alert',
            };

            $trace = array_reverse(debug_backtrace());
            array_pop($trace);

            $error_message = 'Backtrace from ' . $error_type . ': ' . $message . ': in ' . $file . ' on line ' . $line;
            $log->log($error_type, $error_message, $trace);

            if ($display) {
                echo '<p class="error_backtrace">' . $log->text($error_type, $error_message, $trace) . '</p>';
            } else {
                header('Location: error.html');
                exit();
            }

            return true;
        });

        // Обработчик исключений
        set_exception_handler(function(\Throwable $e) use ($log, $display): void {
            $trace = array_reverse(debug_backtrace());
            array_pop($trace);

            $exception_message = 'Backtrace from Exception: ' . $e->getMessage() . ': in ' . $e->getFile() . ' on line ' . $e->getLine();
            $log->critical($exception_message, $trace);

            if ($display) {
                echo '<p class="error_backtrace">' . $log->text('exception', $exception_message, $trace) . '</p>';
            } else {
                header('Location: error.html');
                exit();
            }
        });
    }

    private function registerEvents(): void {
        // Регистрация событий
        if ($this->_config['action_event']) {
            foreach ($this->_config['action_event'] as $key => $value) {
                foreach ($value as $priority => $action) {
                    $this->_event->register($key, new Action($action), $priority);
                }
            }
        }
    }

    private function startActions(array $actions, array &$args, ?Action $error): ?Action {
        foreach ($actions as $action) {
            $action_instance = new Action($action);
            $next_action = $action_instance->execute($this->_registry, $args);

            // Проверка на возвращение следующего действия или исключения
            if ($next_action instanceof Action) {
                return $next_action;
            } elseif ($next_action instanceof \Exception) {
                return $error;
            }
        }
        return null;
    }

    private function dispatch(Action &$next_action, array &$args, Action &$error_action): void {
        // Выполнение действий
        while ($next_action) {
            $output = $next_action->execute($this->_registry, $args);
            $next_action = null; // Обнуляем для предотвращения зацикливания

            if ($output instanceof Action) {
                $next_action = $output; // Продолжаем цикл с новым действием
            } elseif ($output instanceof \Exception) {
                $next_action = $error_action; // Возвращаем действие ошибки
                $error_action = null; // Чтобы избежать зацикливания
            }
        }
    }

    private function output(): void {
        // Установка заголовков ответа
        foreach ($this->_config['response_header'] as $header) {
            $this->_response->addHeader($header);
        }

        // Дополнительные заголовки
        $this->setAdditionalHeaders();

        $this->_response->setCompression((int)$this->_config['response_compression']);
        $this->_response->output();
    }

    private function setAdditionalHeaders(): void {
        $this->_response->addHeader('Access-Control-Allow-Origin: *');
        $this->_response->addHeader('Access-Control-Allow-Credentials: true');
        $this->_response->addHeader('Access-Control-Max-Age: 1000');
        $this->_response->addHeader('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding');
        $this->_response->addHeader('Access-Control-Allow-Methods: PUT, POST, GET, OPTIONS, DELETE');
        $this->_response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->_response->addHeader('Pragma: no-cache');
    }
}

