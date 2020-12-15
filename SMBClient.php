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
    const ERROR_DIRECTORY_EXISTS = 17;

    public $dirRoot;
    public $user;
    public $password;
    protected $state;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->state = smbclient_state_new();

        if(isset($config['setOptions'])) {
            foreach ($config['setOptions'] as $option => $value) {
                smbclient_option_set($this->state, $option, $value);
            }
        }

        smbclient_state_init($this->state, null, $this->user, $this->password);

    }

    public function __destruct()
    {
        smbclient_state_free($this->state);
    }

    public function opendir($uri)
    {

        return smbclient_opendir($this->state, self::DIR_ROOT.$uri);
    }

    public function readdir($dir)
    {

        return smbclient_readdir($this->state, $dir);
    }

    public function closedir($dir)
    {

        return smbclient_closedir($this->state, $dir);
    }

    public function unlink($uri)
    {

        return smbclient_unlink($this->state, self::DIR_ROOT.$uri);
    }

    public function mkdir($uri)
    {

        try {

            return smbclient_mkdir($this->state, self::DIR_ROOT.$uri);

        } catch (\Exception $e) {

            if($this->state_errno() == self::ERROR_DIRECTORY_EXISTS) {
                return true;
            }

            \Yii::info('! Model number '.$this->id.': error '. $e->getMessage());
            return false;

        }

    }

    public function rmdir($uri)
    {

        return smbclient_rmdir($this->state, self::DIR_ROOT.$uri);
    }

    public function stat($uri)
    {

        return smbclient_stat($this->state, self::DIR_ROOT.$uri);
    }

    public function fstat(resource $file)
    {

        return smbclient_fstat($this->state, $file);
    }

    public function create($uri)
    {

        return smbclient_creat($this->state, self::DIR_ROOT.$uri);
    }

    public function open($uri)
    {

        return smbclient_open($this->state, self::DIR_ROOT.$uri, 'r');
    }

    public function read($file, $bytes = 10000)
    {

        return smbclient_read($this->state, $file, $bytes);
    }

    public function write($file, $data)
    {

        return smbclient_write($this->state, $file, $data);
    }

    public function close($file)
    {

        return smbclient_close($this->state, $file);
    }

    public function state_errno()
    {

        return smbclient_state_errno($this->state);
    }

    public function createFile($uri, $data)
    {

        $file = $this->create($uri);
        $writeState = $this->write($file, $data);
        $this->close($file);

        if($writeState)
            return true;
        else
            return false;
    }

}