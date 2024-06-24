<?php
class News {
   private string $id;
   private string $title;
   private string $description;

    /**
     * @var Source[]
     */
   private array $sourses = [];
}

class Source {
    private string $id;
    /**
     * @var Publisher[]
     */
    private array $publishers = [];
    private string $link;
    private string $type; //XML HTML JSON
    private string $summary;
}

class Publisher{
    private string $id;
    private string $name;
    private string $logo;


}

interface ParserInterface {
    public function key(): string;
    public function parseNews(string $xml):array;
}


interface DataFormInterface {   //Промежуточный слой
    public function NewsTransform(array $news):array;
}

interface DataSaveInterface {
    public function NewsSave(array $news):bool;
}

interface ParserFactoryInterface
{
    public function createParser(): ParserInterface;
}

class NullParser implements ParserInterface {
    public function __construct(
        private readonly Source $source
    ){}

    public function key(): string{
        return $this->source->getName();
    }

    public function parseNews(string $xml): array
    {
        return [];
    }
}

class newsDTO {
    private string $id;
    private string $title;
    private string $description;
}

class RSSParser implements ParserInterface {
    public function key(): string
    {
        return $this->source->getName();
    }

    public function parseNews(string $xml): array
    {
        $news_array = [];
        $news = simplexml_load_string($xml);
        if (isset($news->channel) && isset($news->channel->item)) {
            foreach ($news->channel->item as $item) {
                $news_array[] = $item;
            }
        }

        return $news_array;

    }
}

class RSSParserFactory implements ParserFactoryInterface {
    public function createParser(): ParserInterface
    {
        return new RSSParser();
    }
}

function createObject(ParserFactoryInterface $factory) {
    $parser = $factory->createParser();
    return $parser;
}

//class ScrapeParseFactory implements ParserFactoryInterface {
//    public function createParser():
//    {
//        return [];
//    }
//}

$xml = <<<XML
<?xml version='1.0' ?> 
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <language>ru</language>
        <title>Lenta.ru : Новости</title>
        <description>Новости, статьи, фотографии, видео. Семь дней в неделю, 24 часа в сутки.</description>
        <link>https://lenta.ru</link>
        <image>
            <url>https://lenta.ru/images/small_logo.png</url>
            <title>Lenta.ru</title>
            <link>https://lenta.ru</link>
            <width>134</width>
            <height>22</height>
        </image>
        <atom:link rel="self" type="application/rss+xml" href="http://lenta.ru/rss"/>
        <item>
            <guid>https://lenta.ru/news/2024/06/22/magate-proinformiruyut-ob-atake-vsu-na-podstantsiyu-v-energodare/</guid>
            <author>Марина Совина</author>
            <title>МАГАТЭ проинформируют об атаке ВСУ на подстанцию в Энергодаре</title>
            <link>https://lenta.ru/news/2024/06/22/magate-proinformiruyut-ob-atake-vsu-na-podstantsiyu-v-energodare/</link>
            <description><![CDATA[]]></description>
            <pubDate>Sat, 22 Jun 2024 00:39:00 +0300</pubDate>
            <enclosure url="https://icdn.lenta.ru/images/2024/06/22/00/20240622005217597/pic_d7992d3730447cd0244ec7f932597961.jpg" type="image/jpeg" length="45167"/>
            <category>Мир</category>
        </item>
    </channel>
    </rss>
XML;


$parserFactory = new RSSParserFactory();
$parser = createObject($parserFactory);
$news = $parser->parseNews($xml);

var_dump($news);