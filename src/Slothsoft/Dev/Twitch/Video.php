<?php
declare(strict_types = 1);
namespace Slothsoft\Dev\Twitch;

class Video
{

    protected $id;

    protected $viewURL;

    protected $streamURL;

    protected $streamData;

    protected $chapterURL;

    protected $chapterData;

    public function __construct($id)
    {
        $this->id = $id;
        $this->streamURL = sprintf('https://api.twitch.tv/kraken/videos/%s/', urlencode($this->id));
        
        $this->streamData = Manager::downloadData($this->streamURL);
        
        $this->viewURL = $this->streamData['url'];
        
        $this->chapterURL = sprintf('http://slothsoft.net/getData.php/lib/youtube-dl?url=%s', urlencode($this->viewURL));
        $this->chapterData = Manager::downloadData($this->chapterURL);
        if (! isset($this->chapterData['entries'])) {
            $this->chapterData['entries'] = [
                $this->chapterData
            ];
        }
    }

    public function getStreamData()
    {
        return $this->streamData;
    }

    public function getChapterData()
    {
        return $this->chapterData;
    }
}