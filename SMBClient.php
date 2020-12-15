<?php
namespace dekado\smbclient;

use yii\base\BaseObject;

/**
 * Class SMBClient
 * Подключение к общей папке на Windows машине с Linux php приложения.
 * Для работы требуется установить smbclient и php расширение libsmbclient
 * https://github.com/eduardok/libsmbclient-php
 */
class SMBClient extends BaseObject
{
    /** @var int Номер ошибки, говорящий о том, что каталог существует */
    const ERROR_DIRECTORY_EXISTS = 17;

    /** @var string Путь в корневому каталогу, в котором будет вестить работа */
    public $dirRoot;
    /** @var string Имя пользователя */
    public $user;
    /** @var string Пароль пользователя */
    public $password;
    /** @var resource Ресурс подключения */
    protected $state;

    /**
     * SMBClient constructor.
     * Инициализация ресурса подключения,
     * установка опций
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->state = \smbclient_state_new();

        if(isset($config['setOptions'])) {
            foreach ($config['setOptions'] as $option => $value) {
                \smbclient_option_set($this->state, $option, $value);
            }
        }

        \smbclient_state_init($this->state, null, $this->user, $this->password);

    }

    /**
     * Закрытие соединения
     */
    public function __destruct()
    {
        \smbclient_state_free($this->state);
    }

    /**
     * Открыть каталог
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return mixed
     */
    public function opendir($uri)
    {
        return \smbclient_opendir($this->state, $this->dirRoot.$uri);
    }

    /**
     * Чтение каталога
     * @param string $dir Адрес каталога
     * @return mixed
     */
    public function readdir($dir)
    {
        return \smbclient_readdir($this->state, $dir);
    }

    /**
     * Закрытие каталога
     * @param $dir Адрес каталога
     * @return mixed
     */
    public function closedir($dir)
    {
        return \smbclient_closedir($this->state, $dir);
    }

    /**
     * Удаление файла или каталога
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return mixed
     */
    public function unlink($uri)
    {
        return \smbclient_unlink($this->state, $this->dirRoot.$uri);
    }

    /**
     * Создание директории,
     * если существует, ничего не делает
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return bool
     */
    public function mkdir($uri)
    {
        try {
            return \smbclient_mkdir($this->state, $this->dirRoot.$uri);
        } catch (\Exception $e) {
            if($this->state_errno() == self::ERROR_DIRECTORY_EXISTS) {
                return true;
            }
            
            return false;
        }

    }

    /**
     * Удаление каталога
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return mixed
     */
    public function rmdir($uri)
    {
        return \smbclient_rmdir($this->state, $this->dirRoot.$uri);
    }

    public function stat($uri)
    {
        return \smbclient_stat($this->state, $this->dirRoot.$uri);
    }

    public function fstat(resource $file)
    {
        return \smbclient_fstat($this->state, $file);
    }

    /**
     * Создание файла
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return mixed
     */
    public function create($uri)
    {
        return \smbclient_creat($this->state, $this->dirRoot.$uri);
    }

    /**
     * Открытие дескриптора на чтение
     * @param string $uri Адрес каталога, относительно $dirRoot
     * @return mixed
     */
    public function open($uri)
    {
        return \smbclient_open($this->state, $this->dirRoot.$uri, 'r');
    }

    /**
     * Чтение файла
     * @param $file
     * @param int $bytes
     * @return mixed
     */
    public function read($file, $bytes = 10000)
    {
        return \smbclient_read($this->state, $file, $bytes);
    }

    /**
     * Сохранение файла
     * @param $file
     * @param string $data Содержимое для записи
     * @return mixed
     */
    public function write($file, $data)
    {
        return \smbclient_write($this->state, $file, $data);
    }

    /**
     * Закрытие дескриптора файла
     * @param $file
     * @return mixed
     */
    public function close($file)
    {
        return \smbclient_close($this->state, $file);
    }

    /**
     * Получение номера ошибки ресурса
     * @return mixed
     */
    public function state_errno()
    {
        return \smbclient_state_errno($this->state);
    }

    /**
     * Созадать файл
     * @param string $uri Адрес файла, относительно $dirRoot
     * @param string $data Содержимое файла
     * @return bool
     */
    public function createFile($uri, $data)
    {
        $file = $this->create($uri);
        $writeState = $this->write($file, $data);
        $this->close($file);

        if($writeState) {
            return true;
        } else {
            return false;
        }
    }

}
