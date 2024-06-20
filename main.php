<?php
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
                    array_push($newsDtos, new NewsDto(uniqid(), $item["title"], $item["description"]));
                }
                return $newsDtos;
            }
        }

        $feed = <<<END
            {
                "title": "Latest News",
                "actions": [
                    {
                        "url": "#",
                        "type": "rss"
                    },
                    {
                        "url": "#",
                        "type": "envelope"
                    }
                ],
                "items": [
                    {
                        "title": "Job shadowing sparks students' career potential",
                        "url": "#",
                        "description": "Job shadow program connects record number of students with potential employers, and helps alumni give back."
                    },
                    {
                        "title": "Breaking down barriers, leading equality",
                        "url": "#",
                        "description": "Fourth-year medical student encourages openness, involvement for LGBTQ community on campus."
                    },
                    {
                        "title": "Surge in designer drugs, tainted 'E' poses lethal risks",
                        "url": "#",
                        "description": "With up to 10 new designer drugs flooding streets every year, more education is needed to convey risks, especially among youth, say UAlberta researchers."
                    },
                    {
                        "title": "UAlberta set to bask in a rainbow of pride",
                        "url": "#",
                        "description": "Beginning Feb. 26, Pride Week celebrates an often invisible campus population."
                    },
                    {
                        "title": "Crowding around campus projects",
                        "url": "#",
                        "description": "U of A looks to crowdfunding to help launch first Alberta-made satellite, support other campus projects."
                    }
                ],
                "moreLink" : {
                    "url": "#",
                    "label":"Read more news"
                }
            }
            END;


            $sourceExample = new Source(uniqid(), ["test"], "test", "json", "test");
            $newsParser = new NewsParser($sourceExample);
            echo var_dump($newsParser->parseNews($feed));