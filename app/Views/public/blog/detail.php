<?php

/**
 * View: Blog Detail
 * Controller: Public\BlogController
 * Description: Halaman detail blog post dengan featured image, social share, related posts
 * 
 * Features:
 * - Hero section dengan featured image
 * - Post meta (author, date, category, views, reading time)
 * - Full content dengan typography
 * - Tags display
 * - Social share buttons
 * - Author info box
 * - Navigation (prev/next post)
 * - Related posts section
 * - Comments section
 * - Sidebar dengan popular posts
 * - Print-friendly layout
 * 
 * @package App\Views\Public\Blog
 * @author  SPK Development Team
 * @version 1.0.0
 */
?>
<?= $this->extend('layouts/public') ?>

<?= $this->section('styles') ?>
<style>
    /* Hero Section */
    .post-hero {
        position: relative;
        height: 450px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        overflow: hidden;
        margin-bottom: 60px;
    }

    .post-hero-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.3;
    }

    .post-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.9) 100%);
    }

    .post-hero-content {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        align-items: center;
        color: white;
    }

    .post-hero h1 {
        font-size: 48px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 20px;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    /* Post Meta */
    .post-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        font-size: 16px;
        opacity: 0.95;
    }

    .post-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .post-meta-item i {
        font-size: 18px;
    }

    .category-badge {
        background: rgba(255, 255, 255, 0.25);
        padding: 6px 16px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
    }

    /* Back Button */
    .back-to-blog {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 20px;
        opacity: 0.9;
        transition: all 0.3s ease;
    }

    .back-to-blog:hover {
        opacity: 1;
        color: white;
        transform: translateX(-5px);
    }

    /* Post Content */
    .post-content-wrapper {
        background: white;
        margin-top: -80px;
        position: relative;
        z-index: 3;
    }

    .post-content {
        background: white;
        border-radius: 16px;
        padding: 60px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 30px 0;
    }

    .post-content h2 {
        font-size: 32px;
        font-weight: 700;
        margin-top: 40px;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .post-content h3 {
        font-size: 24px;
        font-weight: 600;
        margin-top: 30px;
        margin-bottom: 16px;
        color: #4a5568;
    }

    .post-content p {
        font-size: 18px;
        line-height: 1.8;
        color: #4a5568;
        margin-bottom: 20px;
    }

    .post-content ul,
    .post-content ol {
        font-size: 18px;
        line-height: 1.8;
        color: #4a5568;
        margin: 20px 0;
        padding-left: 30px;
    }

    .post-content li {
        margin-bottom: 12px;
    }

    .post-content blockquote {
        border-left: 4px solid #667eea;
        padding-left: 24px;
        margin: 30px 0;
        font-size: 20px;
        font-style: italic;
        color: #667eea;
    }

    .post-content code {
        background: #f7fafc;
        padding: 3px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        color: #e53e3e;
        font-size: 16px;
    }

    .post-content pre {
        background: #2d3748;
        color: #f7fafc;
        padding: 20px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 30px 0;
    }

    .post-content pre code {
        background: none;
        color: inherit;
        padding: 0;
    }

    /* Tags */
    .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin: 40px 0;
        padding: 30px 0;
        border-top: 2px solid #e2e8f0;
        border-bottom: 2px solid #e2e8f0;
    }

    .tag-item {
        display: inline-flex;
        align-items: center;
        background: #f7fafc;
        color: #4a5568;
        padding: 8px 16px;
        border-radius: 50px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .tag-item:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .tag-item i {
        margin-right: 6px;
        font-size: 12px;
    }

    /* Social Share */
    .social-share {
        background: #f7fafc;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 40px;
    }

    .social-share h4 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .share-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .share-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 50px;
        text-decoration: none;
        color: white;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        color: white;
    }

    .share-btn i {
        font-size: 18px;
    }

    .share-btn.facebook {
        background: #1877f2;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.whatsapp {
        background: #25d366;
    }

    .share-btn.linkedin {
        background: #0a66c2;
    }

    .share-btn.copy {
        background: #718096;
    }

    /* Author Box */
    .author-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 40px;
        color: white;
    }

    .author-info {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .author-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.3);
    }

    .author-details h4 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .author-details p {
        opacity: 0.9;
        margin: 0;
        line-height: 1.6;
    }

    /* Post Navigation */
    .post-navigation {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 40px;
    }

    .nav-item {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .nav-item:hover {
        border-color: #667eea;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        transform: translateY(-2px);
    }

    .nav-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #a0aec0;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .nav-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
    }

    .nav-item.next {
        text-align: right;
    }

    /* Related Posts */
    .related-posts {
        margin-bottom: 60px;
    }

    .related-posts h3 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 30px;
        text-align: center;
        color: #2d3748;
    }

    .related-posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
    }

    .related-post-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
    }

    .related-post-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .related-post-image {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }

    .related-post-content {
        padding: 20px;
    }

    .related-post-title {
        font-size: 18px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .related-post-meta {
        font-size: 14px;
        color: #a0aec0;
    }

    /* Sidebar */
    .sidebar-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .sidebar-card h4 {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #2d3748;
    }

    .sidebar-post {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        text-decoration: none;
        padding-bottom: 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .sidebar-post:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .sidebar-post:hover .sidebar-post-title {
        color: #667eea;
    }

    .sidebar-post-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .sidebar-post-title {
        font-size: 16px;
        font-weight: 600;
        color: #2d3748;
        line-height: 1.4;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .sidebar-post-date {
        font-size: 13px;
        color: #a0aec0;
    }

    /* Comments Section */
    .comments-section {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
    }

    .comments-section h3 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #2d3748;
    }

    .comment-item {
        padding: 24px;
        background: #f7fafc;
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .comment-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 16px;
    }

    .comment-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .comment-author {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 4px;
    }

    .comment-date {
        font-size: 13px;
        color: #a0aec0;
    }

    .comment-body {
        color: #4a5568;
        line-height: 1.6;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .post-hero {
            height: 350px;
        }

        .post-hero h1 {
            font-size: 36px;
        }

        .post-content {
            padding: 40px 30px;
        }

        .post-navigation {
            grid-template-columns: 1fr;
        }

        .nav-item.next {
            text-align: left;
        }

        .author-info {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 767px) {
        .post-hero {
            height: 300px;
        }

        .post-hero h1 {
            font-size: 28px;
        }

        .post-content {
            padding: 30px 20px;
            margin-top: -60px;
        }

        .post-content h2 {
            font-size: 24px;
        }

        .post-content p {
            font-size: 16px;
        }

        .related-posts-grid {
            grid-template-columns: 1fr;
        }

        .share-buttons {
            flex-direction: column;
        }

        .share-btn {
            justify-content: center;
        }
    }

    /* Print Styles */
    @media print {

        .post-hero,
        .social-share,
        .post-navigation,
        .related-posts,
        .comments-section,
        .sidebar-card,
        .back-to-blog {
            display: none;
        }

        .post-content {
            box-shadow: none;
            margin: 0;
            padding: 0;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="post-hero">
    <?php if (!empty($post->featured_image)): ?>
        <img src="<?= esc($post->featured_image) ?>" alt="<?= esc($post->title) ?>" class="post-hero-image">
    <?php endif; ?>

    <div class="post-hero-overlay"></div>

    <div class="post-hero-content">
        <div class="container">
            <a href="<?= base_url('blog') ?>" class="back-to-blog">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Blog
            </a>

            <?php if (!empty($post->category_name)): ?>
                <div class="category-badge">
                    <?= esc($post->category_name) ?>
                </div>
            <?php endif; ?>

            <h1><?= esc($post->title) ?></h1>

            <div class="post-meta">
                <div class="post-meta-item">
                    <i class="bi bi-person-circle"></i>
                    <span><?= esc($post->author_name ?? 'Admin SPK') ?></span>
                </div>
                <div class="post-meta-item">
                    <i class="bi bi-calendar3"></i>
                    <span><?= date('d F Y', strtotime($post->published_at)) ?></span>
                </div>
                <div class="post-meta-item">
                    <i class="bi bi-eye"></i>
                    <span><?= number_format($post->views ?? 0) ?> views</span>
                </div>
                <?php if (!empty($post->reading_time)): ?>
                    <div class="post-meta-item">
                        <i class="bi bi-clock"></i>
                        <span><?= $post->reading_time ?> min read</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Post Content -->
<div class="post-content-wrapper">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Post Body -->
                <div class="post-content">
                    <?php if (!empty($post->excerpt)): ?>
                        <p class="lead" style="font-size: 22px; font-weight: 500; color: #667eea; margin-bottom: 30px;">
                            <?= esc($post->excerpt) ?>
                        </p>
                    <?php endif; ?>

                    <?= $post->content ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($post->tags)): ?>
                    <div class="post-tags">
                        <?php
                        $tags = is_array($post->tags) ? $post->tags : explode(',', $post->tags);
                        foreach ($tags as $tag):
                            $tag = trim($tag);
                            $tagSlug = strtolower(str_replace(' ', '-', $tag));
                        ?>
                            <a href="<?= base_url('blog/tag/' . $tagSlug) ?>" class="tag-item">
                                <i class="bi bi-tag"></i>
                                <?= esc($tag) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Social Share -->
                <div class="social-share">
                    <h4><i class="bi bi-share"></i> Bagikan Artikel Ini</h4>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>"
                            target="_blank"
                            class="share-btn facebook">
                            <i class="bi bi-facebook"></i>
                            Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($post->title) ?>"
                            target="_blank"
                            class="share-btn twitter">
                            <i class="bi bi-twitter"></i>
                            Twitter
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($post->title . ' - ' . current_url()) ?>"
                            target="_blank"
                            class="share-btn whatsapp">
                            <i class="bi bi-whatsapp"></i>
                            WhatsApp
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(current_url()) ?>&title=<?= urlencode($post->title) ?>"
                            target="_blank"
                            class="share-btn linkedin">
                            <i class="bi bi-linkedin"></i>
                            LinkedIn
                        </a>
                        <button onclick="copyToClipboard('<?= current_url() ?>')" class="share-btn copy">
                            <i class="bi bi-link-45deg"></i>
                            Salin Link
                        </button>
                    </div>
                </div>

                <!-- Author Box -->
                <?php if (!empty($post->author_name)): ?>
                    <div class="author-box">
                        <div class="author-info">
                            <img src="<?= $post->author_avatar ?? base_url('assets/images/default-avatar.png') ?>"
                                alt="<?= esc($post->author_name) ?>"
                                class="author-avatar">
                            <div class="author-details">
                                <h4><?= esc($post->author_name) ?></h4>
                                <p><?= esc($post->author_bio ?? 'Penulis di Blog Serikat Pekerja Kampus') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Post Navigation -->
                <div class="post-navigation">
                    <?php if (!empty($previousPost)): ?>
                        <a href="<?= base_url('blog/' . $previousPost->slug) ?>" class="nav-item prev">
                            <div class="nav-label">
                                <i class="bi bi-chevron-left"></i>
                                Artikel Sebelumnya
                            </div>
                            <div class="nav-title"><?= esc($previousPost->title) ?></div>
                        </a>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <?php if (!empty($nextPost)): ?>
                        <a href="<?= base_url('blog/' . $nextPost->slug) ?>" class="nav-item next">
                            <div class="nav-label">
                                Artikel Selanjutnya
                                <i class="bi bi-chevron-right"></i>
                            </div>
                            <div class="nav-title"><?= esc($nextPost->title) ?></div>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Comments Section -->
                <?php if (!empty($comments) && is_array($comments)): ?>
                    <div class="comments-section">
                        <h3>
                            <i class="bi bi-chat-left-text"></i>
                            Komentar (<?= $commentsCount ?>)
                        </h3>

                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-header">
                                    <img src="<?= $comment->user_avatar ?? base_url('assets/images/default-avatar.png') ?>"
                                        alt="<?= esc($comment->user_name) ?>"
                                        class="comment-avatar">
                                    <div>
                                        <div class="comment-author"><?= esc($comment->user_name) ?></div>
                                        <div class="comment-date">
                                            <?= date('d F Y, H:i', strtotime($comment->created_at)) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="comment-body">
                                    <?= nl2br(esc($comment->comment)) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Related Posts -->
                <?php if (!empty($relatedPosts) && is_array($relatedPosts) && count($relatedPosts) > 0): ?>
                    <div class="related-posts">
                        <h3>Artikel Terkait</h3>
                        <div class="related-posts-grid">
                            <?php foreach ($relatedPosts as $related): ?>
                                <a href="<?= base_url('blog/' . $related->slug) ?>" class="related-post-card">
                                    <?php if (!empty($related->featured_image)): ?>
                                        <img src="<?= esc($related->featured_image) ?>"
                                            alt="<?= esc($related->title) ?>"
                                            class="related-post-image">
                                    <?php else: ?>
                                        <div class="related-post-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                    <?php endif; ?>

                                    <div class="related-post-content">
                                        <div class="related-post-title"><?= esc($related->title) ?></div>
                                        <div class="related-post-meta">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('d M Y', strtotime($related->published_at)) ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Popular Posts -->
                <?php if (!empty($popularPosts) && is_array($popularPosts)): ?>
                    <div class="sidebar-card">
                        <h4><i class="bi bi-fire"></i> Artikel Populer</h4>
                        <?php foreach ($popularPosts as $popular): ?>
                            <a href="<?= base_url('blog/' . $popular->slug) ?>" class="sidebar-post">
                                <?php if (!empty($popular->featured_image)): ?>
                                    <img src="<?= esc($popular->featured_image) ?>"
                                        alt="<?= esc($popular->title) ?>"
                                        class="sidebar-post-image">
                                <?php else: ?>
                                    <div class="sidebar-post-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                                <?php endif; ?>
                                <div>
                                    <div class="sidebar-post-title"><?= esc($popular->title) ?></div>
                                    <div class="sidebar-post-date">
                                        <?= date('d M Y', strtotime($popular->published_at)) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Categories -->
                <?php if (!empty($categories) && is_array($categories)): ?>
                    <div class="sidebar-card">
                        <h4><i class="bi bi-folder"></i> Kategori</h4>
                        <div class="list-group">
                            <?php foreach ($categories as $category): ?>
                                <a href="<?= base_url('blog/category/' . $category->slug) ?>"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <?= esc($category->name) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $category->posts_count ?? 0 ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Copy to clipboard function
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link berhasil disalin!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Link berhasil disalin!');
            } catch (err) {
                console.error('Fallback: Could not copy text', err);
            }
            document.body.removeChild(textArea);
        }
    }

    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.comment-item, .related-post-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
</script>
<?= $this->endSection() ?>