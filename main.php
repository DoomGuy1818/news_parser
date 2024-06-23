<?php
        require "vendor/autoload.php";

        use Symfony\Component\BrowserKit\HttpBrowser;

        $client = new HttpBrowser();

        class News {
           private string $id;
           private string $title;
           private string $description;

            /**
             * @var Source[]
             */
           private array $sources = [];
        }

        class Source {
            public function __construct( 
                private string $id,
                /**
                 * @var Publisher[]
                 */
                private array $publishers = [],
                private string $link,
                private string $type, //XML HTML JSON
                private string $summary,
            ) {}
            public function type(): string{
                return $this->type;
            }
        }


        class ScrapeSource extends Source {
            public function __construct( 
                private string $id,
                /**
                 * @var Publisher[]
                 */
                private array $publishers = [],
                private string $link,
                private string $type, //URL
                private string $summary,
                private array $selectors = [],
            ) {
                $this->selectors["link"] = $this->selectors[0];
                $this->selectors["title"] = $this->selectors[1];
                $this->selectors["description"] = $this->selectors[2];
            }
            public function getSelector($selector): string{
                return $this->selectors[$selector];
            }
        }

        class Publisher{
            private string $id;
            private string $name;
            private string $logo;


        }



        interface ParserInterface {
            public function key(): string;
            public function parseNews(string $xml):array;
            public function toDTO(array $items):array;
        }


        interface DataFormInterface {   //Промежуточный слой
            public function NewsTransform(array $news):array;
        }

        interface DataSaveInterface {
            public function NewsSave(array $news):bool;
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
            public function toDTO(array $items): array {
                return [];
            }
        }

        class newsDTO {
        public function __construct( 
            private string $id,
            private string $title,
            private string $description,
            private ?string $link,
            ) {}
        }


        class NewsParser implements ParserInterface {
            public function __construct(
                private readonly Source $source
            ){}

            public function key(): string{
                return $this->source->getName();
            }

            public function parseNews(string $news_feed): array
            {
                switch ($this->source->type()) {
                    case "xml":
                        return [];
                    case "json":
                        return ($this->toDto(json_decode($news_feed, true)["items"]));
                    default:
                        return [];
                }
            }

            public function toDto(array $items): array {
                $newsDtos = [];
                foreach ($items as $item) {
                    array_push($newsDtos, new NewsDto(uniqid(), $item["title"], $item["description"], $item["url"]));
                }
                return $newsDtos;
            }
        }


        class NewsURLScraper implements ParserInterface {
            public function __construct(
                private readonly ScrapeSource $source,
                private HttpBrowser $client
            ){}

            public function key(): string{
                return $this->source->getName();
            }

            public function parseNews(string $news_feed): array
            {
                $items = [];

                $crawler = $this->client->request('GET', $news_feed);
                $links = $crawler->filter($this->source->getSelector("link"))->links();

                foreach ($links as $link) {
                    $crawler = $this->client->request('GET', $link->getUri());


                    $crawler->filter(
                        $this->source->getSelector("title"))->each(function ($node) use (&$title) {
                            $title = $node->text();
                    });

                    $crawler->filter(
                        $this->source->getSelector("description"))->each(function ($node) use (&$description) {
                            $description = $node->text();
                    });
                    array_push(
                        $items, 
                        [
                            "title" => $title, 
                            "description" => $description, 
                            "link" => $link->getUri(),
                        ]);
                }

                return ($this->toDto($items));
            }

            public function toDto(array $items): array {
                $newsDtos = [];
                foreach ($items as $item) {
                    array_push($newsDtos, new NewsDto(uniqid(), $item["title"], $item["description"], $item["link"]));
                }
                return $newsDtos;
            }
        }

        $sourceExample = new ScrapeSource(
            uniqid(), 
            ["test"], 
            "test", 
            "url", 
            "test",
            [
                "div.main__list\ js-main-news-list a.main__feed__link\ js-yandex-counter\ js-visited",
                "h1[itemprop=\"headline\"]",
                "div[itemprop=\"articleBody\"] p"
            ]
        );
        $newsScraper = new NewsURLScraper($sourceExample, $client);
        echo var_dump($newsScraper->parseNews("https://t.rbc.ru/"));