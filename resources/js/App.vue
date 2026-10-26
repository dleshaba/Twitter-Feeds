<template>
    <main class="page">
        <header class="header">
            <h1>Twitter Feeds</h1>
            <p>Explore posts in one place.</p>
        </header>

        <section class="feed">
            <h2>Latest Posts</h2>

            <article class="post">
                <div class="avatar">TL</div>

                <div class="post-content">
                    <h3>Tshepo Leshabane <span>@tshepoleshabane</span></h3>
                    <p>
                        Welcome to Twitter Feeds! This is our first post
                        displayed using Vue.js.
                    </p>
                    <small>Just now</small>
                </div>
            </article>

            <p class="note">
                Sample post.
            </p>
        </section>
    </main>
</template>

<script setup>
</script>

<style scoped>
.page {
    max-width: 720px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: Arial, sans-serif;
    color: #17202a;
}

.header {
    padding-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.header h1 {
    margin-bottom: 8px;
}

.header p,
.post-content h3 span,
.post-content small,
.note {
    color: #657786;
}

.feed {
    margin-top: 24px;
}

.post {
    display: flex;
    gap: 14px;
    padding: 20px 0;
    border-bottom: 1px solid #e5e7eb;
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: bold;
}

.post-content h3 {
    margin: 0 0 10px;
    font-size: 16px;
}

.post-content h3 span {
    font-weight: normal;
    font-size: 14px;
}

.post-content p {
    line-height: 1.6;
}

.note {
    margin-top: 20px;
    font-size: 13px;
}
</style>