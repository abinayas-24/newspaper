<?php
class RSSFeedHandler {
    private $feeds = [
        'top_stories' => 'https://timesofindia.indiatimes.com/rssfeedstopstories.cms',
        'india' => 'https://timesofindia.indiatimes.com/rssfeeds/296589292.cms',
        'business' => 'https://timesofindia.indiatimes.com/rssfeeds/1898055.cms',
        'sports' => 'https://timesofindia.indiatimes.com/rssfeeds/4719148.cms',
        'agriculture' => 'https://timesofindia.indiatimes.com/rssfeeds/4719161.cms',
        'climate' => 'https://timesofindia.indiatimes.com/rssfeeds/2647163.cms' // Climate & Environment news feed
    ];

    private $categoryImages = [
        'top_stories' => [
            'https://images.unsplash.com/photo-1504711434969-e33886168f5c',
            'https://images.unsplash.com/photo-1495020689067-958852a7765e',
            'https://images.unsplash.com/photo-1434030216411-0b793f4b4173'
        ],
        'india' => [
            'https://images.unsplash.com/photo-1528181304800-259b08848526',
            'https://images.unsplash.com/photo-1520052203542-d3095fadd8ac',
            'https://images.unsplash.com/photo-1520052205864-92d242b3a76b'
        ],
        'business' => [
            'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40',
            'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
            'https://images.unsplash.com/photo-1507679799987-73729715c926'
        ],
        'sports' => [
            'https://images.unsplash.com/photo-1517649763962-0c1412d4d5f2',
            'https://images.unsplash.com/photo-1517649763962-0c1412d4d5f2',
            'https://images.unsplash.com/photo-1517649763962-0c1412d4d5f2'
        ],
        'agriculture' => [
            'https://images.unsplash.com/photo-1500382017468-9049fed747ef',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30',
            'https://images.unsplash.com/photo-1532938911079-1b94ac4007a8',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655'
        ],
        'climate' => [
            'https://images.unsplash.com/photo-1508784411316-02b8cd4d3a3a',
            'https://images.unsplash.com/photo-1518176258769-f227c798150e',
            'https://images.unsplash.com/photo-1518176258769-f227c798150e',
            'https://images.unsplash.com/photo-1518176258769-f227c798150e',
            'https://images.unsplash.com/photo-1518176258769-f227c798150e'
        ],
        'earthquake' => [
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655',
            'https://images.unsplash.com/photo-1518977676601-b53f82aba655'
        ]
    ];

    public function fetchFeed($category = 'top_stories') {
        if (!isset($this->feeds[$category])) {
            return [];
        }

        $feedUrl = $this->feeds[$category];
        $xml = @simplexml_load_file($feedUrl);
        
        if ($xml === false) {
            error_log("Error loading RSS feed: " . $feedUrl);
            return [];
        }

        $news = [];
        $imageIndex = 0;
        foreach ($xml->channel->item as $item) {
            $description = (string)$item->description;
            $image = $this->getImageForCategory($category, $description);
            
            $news[] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => $description,
                'pubDate' => (string)$item->pubDate,
                'category' => $category,
                'source' => 'Times of India',
                'summary' => $this->generateSummary($description),
                'sentiment' => $this->analyzeSentiment($description),
                'entities' => $this->extractEntities($description),
                'image' => $image
            ];
        }

        return $news;
    }

    private function getCategoryImages($category) {
        return $this->categoryImages[$category] ?? [$this->getDefaultImage($category)];
    }

    private function getDefaultImage($category) {
        $defaultImages = [
            'top_stories' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'india' => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'business' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'sports' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80',
            'agriculture' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
        ];
        return $defaultImages[$category] ?? 'https://via.placeholder.com/300x200?text=News';
    }

    private function getImageForCategory($category, $description) {
        $images = $this->getCategoryImages($category);
        
        // Check for earthquake-related content in any category
        $earthquakeKeywords = [
            'earthquake' => ['earthquake', 'seismic', 'tremor', 'quake', 'magnitude', 'richter', 'epicenter'],
            'damage' => ['damage', 'destruction', 'collapse', 'building', 'structure'],
            'rescue' => ['rescue', 'relief', 'aid', 'emergency', 'disaster'],
            'tsunami' => ['tsunami', 'tidal wave', 'ocean wave'],
            'aftershock' => ['aftershock', 'tremor', 'shock']
        ];
        
        foreach ($earthquakeKeywords as $key => $terms) {
            foreach ($terms as $term) {
                if (stripos($description, $term) !== false) {
                    return $this->getCategoryImages('earthquake')[array_rand($this->getCategoryImages('earthquake'))];
                }
            }
        }
        
        // For climate category, try to find relevant images based on keywords
        if ($category === 'climate') {
            $keywords = [
                'climate' => ['climate', 'global warming', 'temperature'],
                'environment' => ['environment', 'nature', 'ecosystem'],
                'pollution' => ['pollution', 'air quality', 'emissions'],
                'renewable' => ['renewable', 'solar', 'wind', 'energy'],
                'conservation' => ['conservation', 'wildlife', 'biodiversity']
            ];
            
            foreach ($keywords as $key => $terms) {
                foreach ($terms as $term) {
                    if (stripos($description, $term) !== false) {
                        return $images[$key] ?? $images[array_rand($images)];
                    }
                }
            }
        }
        
        // For agriculture category, try to find relevant images based on keywords
        if ($category === 'agriculture') {
            $keywords = [
                'farm' => ['farm', 'farmer', 'farming'],
                'crop' => ['crop', 'harvest', 'field'],
                'livestock' => ['livestock', 'cattle', 'animal'],
                'organic' => ['organic', 'sustainable'],
                'rural' => ['rural', 'village']
            ];
            
            foreach ($keywords as $key => $terms) {
                foreach ($terms as $term) {
                    if (stripos($description, $term) !== false) {
                        return $images[$key] ?? $images[array_rand($images)];
                    }
                }
            }
        }
        
        return $images[array_rand($images)];
    }

    private function generateSummary($description) {
        // Clean HTML tags and limit to 150 characters
        $text = strip_tags($description);
        return substr($text, 0, 150) . (strlen($text) > 150 ? '...' : '');
    }

    private function analyzeSentiment($text) {
        $sentimentKeywords = [
            'positive' => ['success', 'win', 'growth', 'improve', 'benefit', 'support', 'approve', 'praise'],
            'negative' => ['fail', 'loss', 'decline', 'worse', 'harm', 'oppose', 'reject', 'criticize']
        ];

        $score = 0;
        $words = str_word_count(strtolower($text), 1);
        
        foreach ($words as $word) {
            if (in_array($word, $sentimentKeywords['positive'])) {
                $score += 1;
            } elseif (in_array($word, $sentimentKeywords['negative'])) {
                $score -= 1;
            }
        }
        
        $totalWords = count($words);
        if ($totalWords > 0) {
            $score = $score / $totalWords;
        }
        
        return round($score, 2);
    }

    private function extractEntities($text) {
        $entities = [
            'people' => [],
            'states' => []
        ];

        // Extract person names (simple pattern matching)
        preg_match_all('/\b(?:Mr\.|Mrs\.|Dr\.|Shri|Smt\.)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $text, $matches);
        if (!empty($matches[0])) {
            $entities['people'] = array_unique($matches[0]);
        }

        // Extract state names (simple pattern matching)
        preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\s+(?:State|Province|Region)\b/', $text, $matches);
        if (!empty($matches[0])) {
            $entities['states'] = array_unique($matches[0]);
        }

        return $entities;
    }
}
?> 