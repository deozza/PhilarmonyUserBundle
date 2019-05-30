<?php
namespace Deozza\PhilarmonyUserBundle\Service;

use Deozza\PhilarmonyUtils\Exceptions\FileNotFound;
use Symfony\Component\Yaml\Yaml;

class UserSchemaLoader
{

    public function __construct(string $userPath, string $path)
    {
        $this->rootPath = $path;
        $this->userPath = $userPath;
    }

    public function loadUserSchema()
    {
        $user = file_get_contents($this->rootPath.$this->userPath.".yaml");

        try
        {
            $values = Yaml::parse($user);
        }
        catch(\Exception $e)
        {
            throw new FileNotFound();
        }

        if(!isset($values['user']))
        {
            throw new FileNotFound("Root node of ".$this->rootPath.$this->userPath.".yaml must be 'user'.");
        }

        return $values;
    }

    public function loadUserEntityClass()
    {
        return $this->loadUserSchema()['user']['entity'];
    }

    public function loadUserRepositoryClass()
    {
        return $this->loadUserSchema()['user']['repository'];
    }
}