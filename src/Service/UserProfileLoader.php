<?php
namespace Deozza\PhilarmonyUserBundle\Service;

use Deozza\PhilarmonyUserBundle\Exceptions\FileNotFound;
use Symfony\Component\Yaml\Yaml;

class UserProfileLoader
{
    public function __construct(string $userProfile, string $path)
    {
        $this->userProfilePath = $userProfile;
        $this->rootPath = $path;
    }

    public function loadUserProfile()
    {
        $profiles = file_get_contents($this->rootPath.$this->userProfilePath.".yaml");

        try
        {
            $values = Yaml::parse($profiles);
        }
        catch(\Exception $e)
        {
            throw new FileNotFound();
        }

        if(!isset($values['user']))
        {
            throw new FileNotFound("Root node of ".$this->rootPath.$this->userProfilePath.".yaml must be 'user'.");
        }

        return $values['user'];
    }
}