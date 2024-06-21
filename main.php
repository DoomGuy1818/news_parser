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
                $news = simplexml_load_file($xml);
                foreach ($news->item as $new) {
                    array_push($news_array, $news->$new);
                }
                return $news_array;

            }
        }