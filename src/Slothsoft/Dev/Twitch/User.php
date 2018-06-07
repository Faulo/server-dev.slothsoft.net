<?php
declare(strict_types = 1);
namespace Slothsoft\Dev\Twitch;

class User
{

    protected $name;

    protected $viewURL;

    protected $profileURL;

    protected $profileData;

    protected $videoListURL;

    protected $videoListData;

    public function __construct($name)
    {
        $this->name = $name;
        $this->viewURL = sprintf('http://www.twitch.tv/%s/profile/', urlencode($this->name));
        $this->profileURL = sprintf('https://api.twitch.tv/kraken/users/%s/', urlencode($this->name));
        $this->videoListURL = sprintf('https://api.twitch.tv/kraken/channels/%s/videos?broadcasts=true', urlencode($this->name));
        
        $this->profileData = Manager::downloadData($this->profileURL);
        $this->videoListData = Manager::downloadData($this->videoListURL);
        $i = 0;
        while (isset($this->videoListData['_links']['next'])) {
            $url = $this->videoListData['_links']['next'];
            unset($this->videoListData['_links']['next']);
            $data = Manager::downloadData($url);
            if (count($data['videos']) and isset($data['_links']['next'])) {
                $this->videoListData['_links']['next'] = $data['_links']['next'];
            }
            $this->videoListData['videos'] = array_merge($this->videoListData['videos'], $data['videos']);
        }
    }

    public function getProfileData()
    {
        return $this->profileData;
    }

    public function getVideoList()
    {
        $ret = [];
        foreach ($this->videoListData['videos'] as $data) {
            $video = Manager::getVideo($data['_id']);
            $ret[] = $video;
        }
        return $ret;
    }

    public function getVideoListData()
    {
        return $this->videoListData;
    }
}