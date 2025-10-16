<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Services\ContentService;

/**
 * BlogController
 * 
 * Menangani halaman blog public
 * List posts, detail post, filter by tag/category, search
 * 
 * @package App\Controllers\Public
 * @author  SPK Development Team
 * @version 1.0.0
 */
class BlogController extends BaseController
{
    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * Constructor - Dependency Injection
     */
    public function __construct()
    {
        $this->contentService = new ContentService();
    }

    /**
     * Display blog posts list with pagination
     * Supports search and filtering
     * 
     * @return string
     */
    public function index(): string
    {
        try {
            // Get query parameters
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = 12;
            $search = $this->request->getGet('search');
            $category = $this->request->getGet('category');
            $tag = $this->request->getGet('tag');

            // Build filter options
            $options = [
                'page' => $page,
                'limit' => $perPage,
                'order_by' => 'published_at',
                'order_dir' => 'DESC'
            ];

            if ($search) {
                $options['search'] = $search;
            }

            if ($category) {
                $options['category'] = $category;
            }

            if ($tag) {
                $options['tag'] = $tag;
            }

            // Get published posts
            $result = $this->contentService->getPublishedPosts($options);

            // Get popular posts for sidebar
            $popularPosts = $this->contentService->getPopularPosts(['limit' => 5]);

            // Get all categories for filter
            $categories = $this->contentService->getCategories();

            // Get all tags (top 20 most used)
            $tags = $this->contentService->getPopularTags(['limit' => 20]);

            $data = [
                'title' => 'Blog - Serikat Pekerja Kampus',
                'pageTitle' => 'Blog & Berita SPK',
                'metaDescription' => 'Artikel, berita, dan informasi terkini seputar Serikat Pekerja Kampus dan dunia kerja pendidikan tinggi',

                // Posts data
                'posts' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0,

                // Sidebar
                'popularPosts' => $popularPosts['data'] ?? [],
                'categories' => $categories['data'] ?? [],
                'tags' => $tags['data'] ?? [],

                // Filter state
                'currentSearch' => $search,
                'currentCategory' => $category,
                'currentTag' => $tag
            ];

            return view('public/blog/index', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading blog index: ' . $e->getMessage());

            return view('public/blog/index', [
                'title' => 'Blog - SPK',
                'pageTitle' => 'Blog',
                'posts' => [],
                'pager' => null,
                'total' => 0,
                'popularPosts' => [],
                'categories' => [],
                'tags' => [],
                'currentSearch' => null,
                'currentCategory' => null,
                'currentTag' => null
            ]);
        }
    }

    /**
     * Display single blog post detail
     * Increments view count
     * 
     * @param string $slug Post slug
     * @return string
     */
    public function show(string $slug): string
    {
        try {
            // Get post by slug
            $result = $this->contentService->getPostBySlug($slug);

            if (!$result['success'] || !$result['data']) {
                return view('errors/html/error_404');
            }

            $post = $result['data'];

            // Check if post is published
            if ($post->status !== 'published') {
                return view('errors/html/error_404');
            }

            // Increment view count
            $this->contentService->incrementPostViews($post->id);

            // Get related posts (same category or tags)
            $relatedPosts = $this->contentService->getRelatedPosts($post->id, [
                'limit' => 4
            ]);

            // Get previous and next post
            $navigation = $this->contentService->getPostNavigation($post->id);

            // Get post comments (if enabled)
            $comments = $this->getPostComments($post->id);

            $data = [
                'title' => $post->title . ' - Blog SPK',
                'pageTitle' => $post->title,
                'metaDescription' => $post->excerpt ?? strip_tags(substr($post->content, 0, 160)),
                'metaKeywords' => $post->tags ?? '',
                'metaImage' => $post->featured_image ?? null,

                // Post data
                'post' => $post,

                // Related content
                'relatedPosts' => $relatedPosts['data'] ?? [],
                'previousPost' => $navigation['previous'] ?? null,
                'nextPost' => $navigation['next'] ?? null,

                // Comments
                'comments' => $comments,
                'commentsCount' => count($comments),

                // Social share URLs
                'shareUrls' => $this->generateShareUrls($post)
            ];

            return view('public/blog/show', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading blog post: ' . $e->getMessage());
            return view('errors/html/error_404');
        }
    }

    /**
     * Display posts filtered by category
     * 
     * @param string $categorySlug Category slug
     * @return string
     */
    public function category(string $categorySlug): string
    {
        try {
            // Get category info
            $category = $this->contentService->getCategoryBySlug($categorySlug);

            if (!$category['success'] || !$category['data']) {
                return view('errors/html/error_404');
            }

            $categoryData = $category['data'];

            // Get posts in this category
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = 12;

            $result = $this->contentService->getPublishedPosts([
                'page' => $page,
                'limit' => $perPage,
                'category' => $categoryData->slug,
                'order_by' => 'published_at',
                'order_dir' => 'DESC'
            ]);

            $data = [
                'title' => $categoryData->name . ' - Blog SPK',
                'pageTitle' => 'Kategori: ' . $categoryData->name,
                'metaDescription' => $categoryData->description ?? 'Artikel dalam kategori ' . $categoryData->name,

                // Category info
                'category' => $categoryData,

                // Posts
                'posts' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0
            ];

            return view('public/blog/category', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading category: ' . $e->getMessage());
            return view('errors/html/error_404');
        }
    }

    /**
     * Display posts filtered by tag
     * 
     * @param string $tagSlug Tag slug
     * @return string
     */
    public function tag(string $tagSlug): string
    {
        try {
            // Get posts with this tag
            $page = (int) ($this->request->getGet('page') ?? 1);
            $perPage = 12;

            $result = $this->contentService->getPublishedPosts([
                'page' => $page,
                'limit' => $perPage,
                'tag' => $tagSlug,
                'order_by' => 'published_at',
                'order_dir' => 'DESC'
            ]);

            // Format tag name (replace dash with space, capitalize)
            $tagName = ucwords(str_replace('-', ' ', $tagSlug));

            $data = [
                'title' => 'Tag: ' . $tagName . ' - Blog SPK',
                'pageTitle' => 'Tag: ' . $tagName,
                'metaDescription' => 'Artikel dengan tag ' . $tagName,

                // Tag info
                'tag' => $tagSlug,
                'tagName' => $tagName,

                // Posts
                'posts' => $result['data'] ?? [],
                'pager' => $result['pager'] ?? null,
                'total' => $result['total'] ?? 0
            ];

            return view('public/blog/tag', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading tag: ' . $e->getMessage());
            return view('errors/html/error_404');
        }
    }

    /**
     * Search posts (AJAX endpoint)
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function search()
    {
        $query = $this->request->getGet('q');

        if (empty($query) || strlen($query) < 3) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Query minimal 3 karakter',
                'data' => []
            ]);
        }

        try {
            $result = $this->contentService->searchPosts($query, [
                'limit' => 10
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Search completed',
                'data' => $result['data'] ?? []
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error searching posts: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari',
                'data' => []
            ]);
        }
    }

    /**
     * Get RSS feed
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function rss()
    {
        try {
            // Get latest 20 posts
            $result = $this->contentService->getPublishedPosts([
                'limit' => 20,
                'order_by' => 'published_at',
                'order_dir' => 'DESC'
            ]);

            $posts = $result['data'] ?? [];

            // Generate RSS XML
            $rss = $this->generateRSSFeed($posts);

            return $this->response
                ->setContentType('application/rss+xml')
                ->setBody($rss);
        } catch (\Exception $e) {
            log_message('error', 'Error generating RSS feed: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setBody('Error generating RSS feed');
        }
    }

    /**
     * Get post comments
     * 
     * @param int $postId Post ID
     * @return array
     */
    protected function getPostComments(int $postId): array
    {
        try {
            // For now, return empty array
            // TODO: Implement comment system if needed
            return [];
        } catch (\Exception $e) {
            log_message('error', 'Error loading comments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate social share URLs
     * 
     * @param object $post Post entity
     * @return array
     */
    protected function generateShareUrls($post): array
    {
        $postUrl = base_url('blog/' . $post->slug);
        $postTitle = urlencode($post->title);
        $postExcerpt = urlencode($post->excerpt ?? '');

        return [
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $postUrl,
            'twitter' => 'https://twitter.com/intent/tweet?url=' . $postUrl . '&text=' . $postTitle,
            'linkedin' => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $postUrl . '&title=' . $postTitle,
            'whatsapp' => 'https://wa.me/?text=' . $postTitle . ' ' . $postUrl,
            'telegram' => 'https://t.me/share/url?url=' . $postUrl . '&text=' . $postTitle,
            'email' => 'mailto:?subject=' . $postTitle . '&body=' . $postExcerpt . '%0A%0A' . $postUrl
        ];
    }

    /**
     * Generate RSS feed XML
     * 
     * @param array $posts Array of posts
     * @return string XML content
     */
    protected function generateRSSFeed(array $posts): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>Serikat Pekerja Kampus - Blog</title>' . "\n";
        $xml .= '<link>' . base_url('blog') . '</link>' . "\n";
        $xml .= '<description>Artikel, berita, dan informasi terkini dari Serikat Pekerja Kampus</description>' . "\n";
        $xml .= '<language>id</language>' . "\n";
        $xml .= '<lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        $xml .= '<atom:link href="' . base_url('blog/rss') . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($posts as $post) {
            $xml .= '<item>' . "\n";
            $xml .= '<title><![CDATA[' . $post->title . ']]></title>' . "\n";
            $xml .= '<link>' . base_url('blog/' . $post->slug) . '</link>' . "\n";
            $xml .= '<guid>' . base_url('blog/' . $post->slug) . '</guid>' . "\n";
            $xml .= '<description><![CDATA[' . ($post->excerpt ?? strip_tags(substr($post->content, 0, 300))) . ']]></description>' . "\n";
            $xml .= '<pubDate>' . date('r', strtotime($post->published_at)) . '</pubDate>' . "\n";

            if ($post->author_name) {
                $xml .= '<author><![CDATA[' . $post->author_name . ']]></author>' . "\n";
            }

            if ($post->category_name) {
                $xml .= '<category><![CDATA[' . $post->category_name . ']]></category>' . "\n";
            }

            $xml .= '</item>' . "\n";
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';

        return $xml;
    }
}
