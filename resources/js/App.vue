
<template>
    <main class="page">
        <header class="header">
            <h1>Twitter Feeds</h1>
            <p>Explore posts in one place.</p>
        </header>

        <section class="feed">
            <h2>Latest Posts</h2>

            <p v-if="loading">Loading posts...</p>

            <p v-else-if="error" class="error">
                {{ error }}
            </p>

            <p v-else-if="posts.length === 0">
                No posts yet. Be the first to post!
            </p>

            <article
                v-for="post in posts"
                :key="post.id"
                class="post"
            >
                <div class="avatar">
                    <img
                        v-if="post.avatar_url"
                        :src="post.avatar_url"
                        :alt="post.author_name"
                    />
                    <span v-else>{{ getInitials(post.author_name) }}</span>
                </div>

                <div class="post-content">
                    <h3>
                        {{ post.author_name }}
                        <span>{{ post.username }}</span>
                    </h3>

                    <p>{{ post.content }}</p>

                    <small>{{ formatDate(post.posted_at || post.created_at) }}</small>
                </div>
            </article>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Stores the posts received from Laravel.
const posts = ref([]);

// Controls the loading message.
const loading = ref(true);

// Stores an error if the request fails.
const error = ref('');

// Runs automatically when the Vue component loads.
onMounted(async () => {
    try {
        const response = await fetch('/api/posts');

        if (!response.ok) {
            throw new Error('Failed to load posts.');
        }

        posts.value = await response.json();
    } catch (err) {
        console.error(err);
        error.value = 'Could not load posts. Please check your Laravel server.';
    } finally {
        loading.value = false;
    }
});

// Creates initials such as "JD" from "Jane Doe".
function getInitials(name) {
    if (!name) {
        return '?';
    }

    return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

// Converts the database date into a readable date and time.
function formatDate(dateString) {
    if (!dateString) {
        return 'Date unavailable';
    }

    return new Date(dateString).toLocaleString();
}
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
.post-content small {
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
    overflow: hidden;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    font-weight: bold;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.post-content small {
    font-size: 12px;
}

.error {
    color: #dc2626;
}
</style>
