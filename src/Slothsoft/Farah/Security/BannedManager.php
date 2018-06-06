<?php
declare(strict_types = 1);
namespace Slothsoft\Farah\Security;

use Slothsoft\Core\ServerEnvironment;
use Slothsoft\Core\Configuration\ConfigurationField;
use Slothsoft\Core\Configuration\FileConfigurationField;
use Slothsoft\Core\Configuration\ConfigurationRequiredException;

class BannedManager
{

    public static function getInstance(): self
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private static function ipFile(): ConfigurationField
    {
        static $field;
        if ($field === null) {
            try {
                $path = ServerEnvironment::getDataDirectory() . 'banned-ips.txt';
            } catch (ConfigurationRequiredException $e) {
                $path = temp_file(__NAMESPACE__);
            }
            $field = new FileConfigurationField($path);
        }
        return $field;
    }

    public static function setIpFile(string $path)
    {
        self::ipFile()->setValue($path);
    }

    public static function getIpFile(): string
    {
        return self::ipFile()->getValue();
    }

    public function isBanworthy(string $message): bool
    {
        // TODO: proper hate speech check
        return (preg_match('/nigg[ae]/u', $message) or preg_match('/fags/u', $message) or preg_match('/卐/u', $message));
    }

    public function isBanned(string $ip): bool
    {
        return in_array($ip, $this->getBannedList(), true);
    }

    public function addBanned(string $ip)
    {
        $this->setBannedList(array_merge($this->getBannedList(), [
            $ip
        ]));
    }

    public function removeBanned(string $ip)
    {
        $this->setBannedList(array_diff($this->getBannedList(), [
            $ip
        ]));
    }

    private $bannedList;

    private function getBannedList(): array
    {
        if ($this->bannedList === null) {
            $logFile = self::getIpFile();
            $this->bannedList = file_exists($logFile) ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        }
        return $this->bannedList;
    }

    private function setBannedList(array $list)
    {
        $this->bannedList = $list;
        $logFile = self::getIpFile();
        file_put_contents($logFile, implode(PHP_EOL, $this->bannedList));
    }
}

