<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * PageModel
 * 
 * Model untuk mengelola static pages (halaman statis)
 * Digunakan untuk Manifesto, AD/ART, Sejarah SPK, Visi Misi, dll
 * 
 * @package App\Models
 * @author  SPK Development Team
 * @version 1.0.0
 */
class PageModel extends Model
{
    protected $table            = 'pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'slug',
        'content',
        'excerpt',
        'template',
        'status',
        'is_in_menu',
        'menu_order',
        'parent_id',
        'author_id',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'views_count'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'slug' => 'permit_empty|max_length[255]|is_unique[pages.slug,id,{id}]',
        'content' => 'required|min_length[10]',
        'status' => 'permit_empty|in_list[draft,published]',
        'template' => 'permit_empty|max_length[50]',
        'is_in_menu' => 'permit_empty|in_list[0,1]',
        'menu_order' => 'permit_empty|is_natural',
    ];

    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul halaman harus diisi',
            'min_length' => 'Judul minimal 3 karakter',
            'max_length' => 'Judul maksimal 255 karakter',
        ],
        'slug' => [
            'is_unique' => 'Slug sudah digunakan',
        ],
        'content' => [
            'required'   => 'Konten halaman harus diisi',
            'min_length' => 'Konten minimal 10 karakter',
        ],
        'status' => [
            'in_list' => 'Status tidak valid',
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateSlug', 'setDefaults'];
    protected $beforeUpdate   = ['generateSlug'];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get page with author
     * 
     * @return object
     */
    public function withAuthor()
    {
        return $this->select('pages.*, users.username as author_name, users.email as author_email')
            ->join('users', 'users.id = pages.author_id', 'left');
    }

    /**
     * Get page with parent
     * 
     * @return object
     */
    public function withParent()
    {
        return $this->select('pages.*, parent_pages.title as parent_title, parent_pages.slug as parent_slug')
            ->join('pages as parent_pages', 'parent_pages.id = pages.parent_id', 'left');
    }

    /**
     * Get page with children count
     * 
     * @return object
     */
    public function withChildrenCount()
    {
        return $this->select('pages.*')
            ->select('(SELECT COUNT(*) FROM pages as child_pages WHERE child_pages.parent_id = pages.id AND child_pages.deleted_at IS NULL) as children_count');
    }

    /**
     * Get page with complete relations
     * 
     * @return object
     */
    public function withComplete()
    {
        return $this->select('pages.*')
            ->select('users.username as author_name')
            ->select('parent_pages.title as parent_title')
            ->select('(SELECT COUNT(*) FROM pages as child_pages WHERE child_pages.parent_id = pages.id AND child_pages.deleted_at IS NULL) as children_count')
            ->join('users', 'users.id = pages.author_id', 'left')
            ->join('pages as parent_pages', 'parent_pages.id = pages.parent_id', 'left');
    }

    // ========================================
    // SCOPES - FILTERING
    // ========================================

    /**
     * Get published pages only
     * 
     * @return object
     */
    public function published()
    {
        return $this->where('status', 'published');
    }

    /**
     * Get draft pages
     * 
     * @return object
     */
    public function draft()
    {
        return $this->where('status', 'draft');
    }

    /**
     * Get pages in menu
     * 
     * @return object
     */
    public function inMenu()
    {
        return $this->where('is_in_menu', 1);
    }

    /**
     * Get parent pages (top level)
     * 
     * @return object
     */
    public function parentPages()
    {
        return $this->where('parent_id IS NULL');
    }

    /**
     * Get child pages
     * 
     * @param int $parentId Parent page ID
     * @return object
     */
    public function childPages(int $parentId)
    {
        return $this->where('parent_id', $parentId);
    }

    /**
     * Order by menu order
     * 
     * @return object
     */
    public function ordered()
    {
        return $this->orderBy('menu_order', 'ASC')
            ->orderBy('title', 'ASC');
    }

    // ========================================
    // QUERY HELPERS
    // ========================================

    /**
     * Get page by slug
     * 
     * @param string $slug Page slug
     * @return object|null
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get published page by slug
     * 
     * @param string $slug Page slug
     * @return object|null
     */
    public function getPublishedBySlug(string $slug)
    {
        return $this->withAuthor()
            ->published()
            ->where('pages.slug', $slug)
            ->first();
    }

    /**
     * Get pages for menu
     * 
     * @return array
     */
    public function getForMenu(): array
    {
        return $this->published()
            ->inMenu()
            ->parentPages()
            ->ordered()
            ->findAll();
    }

    /**
     * Get menu structure (hierarchical)
     * 
     * @return array
     */
    public function getMenuStructure(): array
    {
        $parents = $this->getForMenu();

        foreach ($parents as &$parent) {
            $parent->children = $this->published()
                ->inMenu()
                ->childPages($parent->id)
                ->ordered()
                ->findAll();
        }

        return $parents;
    }

    /**
     * Get all published pages ordered
     * 
     * @return array
     */
    public function getPublished(): array
    {
        return $this->published()
            ->ordered()
            ->findAll();
    }

    /**
     * Search pages by keyword
     * 
     * @param string $keyword Search keyword
     * @return object
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
            ->like('title', $keyword)
            ->orLike('content', $keyword)
            ->orLike('excerpt', $keyword)
            ->groupEnd();
    }

    /**
     * Get pages by template
     * 
     * @param string $template Template name
     * @return array
     */
    public function getByTemplate(string $template): array
    {
        return $this->where('template', $template)
            ->published()
            ->findAll();
    }

    /**
     * Get static pages (commonly used pages)
     * 
     * @return object|null Returns object with manifesto, ad_art, sejarah, visi_misi
     */
    public function getStaticPages()
    {
        $slugs = ['manifesto', 'ad-art', 'sejarah-spk', 'visi-misi'];
        $pages = $this->published()
            ->whereIn('slug', $slugs)
            ->findAll();

        $result = new \stdClass();
        foreach ($pages as $page) {
            $key = str_replace('-', '_', $page->slug);
            $result->$key = $page;
        }

        return $result;
    }

    /**
     * Get page with children
     * 
     * @param int $pageId Page ID
     * @return object|null
     */
    public function getWithChildren(int $pageId)
    {
        $page = $this->withComplete()->find($pageId);

        if (!$page) {
            return null;
        }

        $page->children = $this->published()
            ->childPages($pageId)
            ->ordered()
            ->findAll();

        return $page;
    }

    // ========================================
    // STATISTICS METHODS
    // ========================================

    /**
     * Count pages by status
     * 
     * @return array
     */
    public function countByStatus(): array
    {
        $result = $this->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->findAll();

        $stats = [
            'draft' => 0,
            'published' => 0,
        ];

        foreach ($result as $row) {
            $stats[$row->status] = (int)$row->count;
        }

        return $stats;
    }

    /**
     * Get total views count
     * 
     * @return int
     */
    public function getTotalViews(): int
    {
        $result = $this->selectSum('views_count')->first();
        return $result ? (int)$result->views_count : 0;
    }

    /**
     * Get most viewed pages
     * 
     * @param int $limit Number of records
     * @return array
     */
    public function getMostViewed(int $limit = 10): array
    {
        return $this->published()
            ->orderBy('views_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Count pages in menu
     * 
     * @return int
     */
    public function countInMenu(): int
    {
        return $this->inMenu()->countAllResults();
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Publish page
     * 
     * @param int $pageId Page ID
     * @return bool
     */
    public function publishPage(int $pageId): bool
    {
        return $this->update($pageId, ['status' => 'published']);
    }

    /**
     * Unpublish page (set to draft)
     * 
     * @param int $pageId Page ID
     * @return bool
     */
    public function unpublishPage(int $pageId): bool
    {
        return $this->update($pageId, ['status' => 'draft']);
    }

    /**
     * Add page to menu
     * 
     * @param int $pageId Page ID
     * @param int|null $order Menu order
     * @return bool
     */
    public function addToMenu(int $pageId, ?int $order = null): bool
    {
        if ($order === null) {
            $maxOrder = $this->selectMax('menu_order')->first();
            $order = $maxOrder ? (int)$maxOrder->menu_order + 1 : 1;
        }

        return $this->update($pageId, [
            'is_in_menu' => 1,
            'menu_order' => $order,
        ]);
    }

    /**
     * Remove page from menu
     * 
     * @param int $pageId Page ID
     * @return bool
     */
    public function removeFromMenu(int $pageId): bool
    {
        return $this->update($pageId, ['is_in_menu' => 0]);
    }

    /**
     * Update menu order
     * 
     * @param int $pageId Page ID
     * @param int $order New order
     * @return bool
     */
    public function updateMenuOrder(int $pageId, int $order): bool
    {
        return $this->update($pageId, ['menu_order' => $order]);
    }

    /**
     * Reorder menu items
     * 
     * @param array $order Array of [id => order]
     * @return bool
     */
    public function reorderMenu(array $order): bool
    {
        $this->db->transStart();

        foreach ($order as $id => $menuOrder) {
            $this->update($id, ['menu_order' => $menuOrder]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Increment views count
     * 
     * @param int $pageId Page ID
     * @return bool
     */
    public function incrementViews(int $pageId): bool
    {
        return $this->set('views_count', 'views_count + 1', false)
            ->where('id', $pageId)
            ->update();
    }

    /**
     * Update page template
     * 
     * @param int $pageId Page ID
     * @param string $template Template name
     * @return bool
     */
    public function updateTemplate(int $pageId, string $template): bool
    {
        return $this->update($pageId, ['template' => $template]);
    }

    /**
     * Set parent page
     * 
     * @param int $pageId Page ID
     * @param int|null $parentId Parent page ID
     * @return bool
     */
    public function setParent(int $pageId, ?int $parentId = null): bool
    {
        // Prevent circular reference
        if ($parentId && $this->isCircularReference($pageId, $parentId)) {
            return false;
        }

        return $this->update($pageId, ['parent_id' => $parentId]);
    }

    /**
     * Check for circular reference in parent-child relationship
     * 
     * @param int $pageId Page ID
     * @param int $parentId Potential parent ID
     * @return bool
     */
    protected function isCircularReference(int $pageId, int $parentId): bool
    {
        // If parent is the page itself
        if ($pageId === $parentId) {
            return true;
        }

        // Check if proposed parent is a descendant of this page
        $parent = $this->find($parentId);
        while ($parent && $parent->parent_id) {
            if ($parent->parent_id === $pageId) {
                return true;
            }
            $parent = $this->find($parent->parent_id);
        }

        return false;
    }

    /**
     * Duplicate page
     * 
     * @param int $pageId Page ID to duplicate
     * @return int|false New page ID or false
     */
    public function duplicatePage(int $pageId)
    {
        $page = $this->find($pageId);

        if (!$page) {
            return false;
        }

        $newPage = [
            'title' => $page->title . ' (Copy)',
            'content' => $page->content,
            'excerpt' => $page->excerpt,
            'template' => $page->template,
            'status' => 'draft',
            'author_id' => $page->author_id,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'featured_image' => $page->featured_image,
        ];

        return $this->insert($newPage);
    }

    /**
     * Get breadcrumb trail for page
     * 
     * @param int $pageId Page ID
     * @return array
     */
    public function getBreadcrumb(int $pageId): array
    {
        $breadcrumb = [];
        $page = $this->find($pageId);

        while ($page) {
            array_unshift($breadcrumb, [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
            ]);

            $page = $page->parent_id ? $this->find($page->parent_id) : null;
        }

        return $breadcrumb;
    }

    // ========================================
    // CALLBACKS
    // ========================================

    /**
     * Generate slug from title
     * 
     * @param array $data
     * @return array
     */
    protected function generateSlug(array $data): array
    {
        if (isset($data['data']['title']) && empty($data['data']['slug'])) {
            $slug = url_title($data['data']['title'], '-', true);

            // Check if slug exists and make it unique
            $count = 1;
            $originalSlug = $slug;
            while ($this->where('slug', $slug)->countAllResults() > 0) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $data['data']['slug'] = $slug;
        }

        return $data;
    }

    /**
     * Set default values before insert
     * 
     * @param array $data
     * @return array
     */
    protected function setDefaults(array $data): array
    {
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'draft';
        }

        if (!isset($data['data']['is_in_menu'])) {
            $data['data']['is_in_menu'] = 0;
        }

        if (!isset($data['data']['views_count'])) {
            $data['data']['views_count'] = 0;
        }

        if (!isset($data['data']['template'])) {
            $data['data']['template'] = 'default';
        }

        // Set menu_order if page is in menu
        if ($data['data']['is_in_menu'] == 1 && empty($data['data']['menu_order'])) {
            $maxOrder = $this->selectMax('menu_order')->first();
            $data['data']['menu_order'] = $maxOrder ? (int)$maxOrder->menu_order + 1 : 1;
        }

        return $data;
    }
}
