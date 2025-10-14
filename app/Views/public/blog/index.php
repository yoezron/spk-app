<?php

/**
 * View: Blog Index
 * Controller: Public\BlogController
 * Description: Halaman list blog posts dengan pagination, search, dan filter
 * 
 * Features:
 * - Grid layout blog posts
 * - Search functionality
 * - Filter by category & tag
 * - Pagination
 * - Sidebar dengan popular posts, categories, tags
 * - Responsive design
 * 
 * @package App\Views\Public\Blog
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('styles') ?>
<style>
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        color: white;
        margin-bottom: 60px;
    }

    .page-header h1 {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .page-header p {
        font-size: 18px;
        opacity: 0.95;
    }

    /* Search & Filter */
    .search-filter-section {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding-left: 45px;
        border-radius: 50px;
        border: 2px solid #e2e8f0;
        height: 50px;
    }

    .search-box input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .search-box i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aec0;
        font-size: 24px;
    }

    .filter-badge {
        display: inline-flex;
        align-items: center;
        background: #667eea;
        color: white;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        margin-right: 8px;
        margin-bottom: 8px;
    }

    .filter-badge i {
        font-size: 18px;
        margin-left: 8px;
        cursor: pointer;
    }

    /* Blog Card */
    .blog-post-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 30px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .blog-post-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .blog-post-image {
        height: 220px;
        background-size: cover;
        background-position: center;
        position: relative;
    }

    .blog-post-category {
        position: absolute;
        top: 16px;
        left: 16px;
        background: #667eea;
        color: white;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
    }

    .blog-post-body {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .blog-post-title {
        font-size: 20px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .blog-post-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .blog-post-title a:hover {
        color: #667eea;
    }

    .blog-post-excerpt {
        color: #718096;
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .blog-post-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        font-size: 13px;
        color: #a0aec0;
    }

    .blog-post-meta i {
        font-size: 16px;
        vertical-align: middle;
        margin-right: 4px;
    }

    .blog-post-meta .meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Sidebar */
    .sidebar-widget {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .sidebar-widget-title {
        font-size: 20px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #667eea;
    }

    /* Popular Posts */
    .popular-post-item {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .popular-post-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .popular-post-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        background-size: cover;
        background-position: center;
        flex-shrink: 0;
    }

    .popular-post-content {
        flex: 1;
    }

    .popular-post-title {
        font-size: 14px;
        font-weight: 500;
        color: #2d3748;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
    }

    .popular-post-title a {
        color: inherit;
        text-decoration: none;
    }

    .popular-post-title a:hover {
        color: #667eea;
    }

    .popular-post-date {
        font-size: 12px;
        color: #a0aec0;
    }

    /* Categories & Tags */
    .category-list,
    .tag-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .category-item {
        margin-bottom: 12px;
    }

    .category-item:last-child {
        margin-bottom: 0;
    }

    .category-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 12px;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .category-link:hover {
        background: #f7fafc;
        color: #667eea;
    }

    .category-link.active {
        background: #667eea;
        color: white;
    }

    .category-count {
        font-size: 12px;
        background: #e2e8f0;
        padding: 2px 8px;
        border-radius: 50px;
    }

    .category-link.active .category-count {
        background: rgba(255, 255, 255, 0.3);
    }

    .tag-item {
        display: inline-block;
        margin: 0 8px 8px 0;
    }

    .tag-link {
        display: inline-block;
        padding: 6px 16px;
        background: #f7fafc;
        border-radius: 50px;
        color: #4a5568;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .tag-link:hover {
        background: #667eea;
        color: white;
    }

    /* Pagination */
    .pagination {
        margin-top: 40px;
    }

    .pagination .page-link {
        border-radius: 8px;
        margin: 0 4px;
        border: 1px solid #e2e8f0;
        color: #4a5568;
    }

    .pagination .page-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .pagination .active .page-link {
        background: #667eea;
        border-color: #667eea;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 80px;
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #4a5568;
        margin-bottom: 12px;
    }

    .empty-state p {
        color: #a0aec0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 32px;
        }

        .blog-post-image {
            height: 180px;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= esc($pageTitle) ?></h1>
        <p>Artikel, berita, dan informasi terkini seputar SPK dan dunia kerja pendidikan tinggi</p>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Search & Filter -->
            <div class="search-filter-section">
                <form action="<?= base_url('blog') ?>" method="GET">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <div class="search-box">
                                <i class="material-icons-outlined">search</i>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="search"
                                    placeholder="Cari artikel..."
                                    value="<?= esc($currentSearch ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="material-icons-outlined align-middle" style="font-size: 20px;">search</i>
                                Cari
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Active Filters -->
                <?php if ($currentSearch || $currentCategory || $currentTag): ?>
                    <div class="mt-3">
                        <small class="text-muted">Filter aktif:</small><br>
                        <?php if ($currentSearch): ?>
                            <span class="filter-badge">
                                Pencarian: "<?= esc($currentSearch) ?>"
                                <a href="<?= base_url('blog') ?>" class="text-white">
                                    <i class="material-icons-outlined">close</i>
                                </a>
                            </span>
                        <?php endif; ?>

                        <?php if ($currentCategory): ?>
                            <span class="filter-badge">
                                Kategori: <?= esc($currentCategory) ?>
                                <a href="<?= base_url('blog') ?>" class="text-white">
                                    <i class="material-icons-outlined">close</i>
                                </a>
                            </span>
                        <?php endif; ?>

                        <?php if ($currentTag): ?>
                            <span class="filter-badge">
                                Tag: <?= esc($currentTag) ?>
                                <a href="<?= base_url('blog') ?>" class="text-white">
                                    <i class="material-icons-outlined">close</i>
                                </a>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Posts Grid -->
            <?php if (!empty($posts)): ?>
                <div class="row">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6">
                            <div class="blog-post-card">
                                <div class="blog-post-image" style="background-image: url('<?= !empty($post->featured_image) ? base_url('uploads/posts/' . esc($post->featured_image)) : base_url('assets/images/blog-placeholder.jpg') ?>');">
                                    <?php if (!empty($post->category_name)): ?>
                                        <span class="blog-post-category"><?= esc($post->category_name) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="blog-post-body">
                                    <h2 class="blog-post-title">
                                        <a href="<?= base_url('blog/' . esc($post->slug)) ?>">
                                            <?= esc($post->title) ?>
                                        </a>
                                    </h2>

                                    <p class="blog-post-excerpt">
                                        <?= esc($post->excerpt ?? strip_tags(substr($post->content, 0, 150))) ?>...
                                    </p>

                                    <div class="blog-post-meta">
                                        <div class="meta-item">
                                            <i class="material-icons-outlined">person</i>
                                            <span><?= esc($post->author_name ?? 'Admin') ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="material-icons-outlined">calendar_today</i>
                                            <span><?= date('d M Y', strtotime($post->published_at ?? $post->created_at)) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pager): ?>
                    <div class="d-flex justify-content-center">
                        <?= $pager->links() ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="material-icons-outlined">article</i>
                    <h3>Tidak Ada Artikel</h3>
                    <p>Belum ada artikel yang dipublikasikan atau tidak ditemukan artikel yang sesuai dengan pencarian Anda.</p>
                    <?php if ($currentSearch || $currentCategory || $currentTag): ?>
                        <a href="<?= base_url('blog') ?>" class="btn btn-outline-primary mt-3">
                            Reset Filter
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Popular Posts -->
            <?php if (!empty($popularPosts)): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">
                        <i class="material-icons-outlined align-middle me-2" style="font-size: 24px;">trending_up</i>
                        Artikel Populer
                    </h3>

                    <?php foreach ($popularPosts as $popularPost): ?>
                        <div class="popular-post-item">
                            <div class="popular-post-image" style="background-image: url('<?= !empty($popularPost->featured_image) ? base_url('uploads/posts/' . esc($popularPost->featured_image)) : base_url('assets/images/blog-placeholder.jpg') ?>');"></div>
                            <div class="popular-post-content">
                                <h4 class="popular-post-title">
                                    <a href="<?= base_url('blog/' . esc($popularPost->slug)) ?>">
                                        <?= esc($popularPost->title) ?>
                                    </a>
                                </h4>
                                <div class="popular-post-date">
                                    <i class="material-icons-outlined" style="font-size: 14px; vertical-align: middle;">calendar_today</i>
                                    <?= date('d M Y', strtotime($popularPost->published_at ?? $popularPost->created_at)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Categories -->
            <?php if (!empty($categories)): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">
                        <i class="material-icons-outlined align-middle me-2" style="font-size: 24px;">folder</i>
                        Kategori
                    </h3>

                    <ul class="category-list">
                        <?php foreach ($categories as $category): ?>
                            <li class="category-item">
                                <a href="<?= base_url('blog?category=' . esc($category->slug)) ?>"
                                    class="category-link <?= ($currentCategory === $category->slug) ? 'active' : '' ?>">
                                    <span><?= esc($category->name) ?></span>
                                    <span class="category-count"><?= $category->post_count ?? 0 ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Tags -->
            <?php if (!empty($tags)): ?>
                <div class="sidebar-widget">
                    <h3 class="sidebar-widget-title">
                        <i class="material-icons-outlined align-middle me-2" style="font-size: 24px;">label</i>
                        Tag Populer
                    </h3>

                    <div class="tag-list">
                        <?php foreach ($tags as $tag): ?>
                            <span class="tag-item">
                                <a href="<?= base_url('blog?tag=' . esc($tag->slug)) ?>" class="tag-link">
                                    <?= esc($tag->name) ?>
                                </a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Smooth scroll to top after filter/search
    if (window.location.search) {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>
<?= $this->endSection() ?>