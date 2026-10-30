
<template>
    <main class="page">
        <header class="header">
            <h1>Twitter Feeds</h1>
            <p>Explore posts in one place.</p>
        </header>
        <section>
            <input type="text" placeholder="Search" v-model="query">
        </section>

        <section class="composer">
            <h2>Create a post</h2>

            <form @submit.prevent="createPost">
                <label for="author">Your name</label>
                <input
                    id="author"
                    v-model="authorName"
                    type="text"
                    placeholder="e.g. Name Surname"
                    maxlength="255"
                    required
                />

                <label for="username">Username</label>
                <input
                    id="username"
                    v-model="username"
                    type="text"
                    placeholder="e.g. @email"
                    maxlength="255"
                    required
                />

                <label for="content">What's happening?</label>
                <textarea
                    id="content"
                    v-model="content"
                    placeholder="Write your post..."
                    maxlength="5000"
                    rows="4"
                    required
                ></textarea>

                <p class="counter">{{ content.length }}/5000</p>

                <p v-if="formError" class="error">{{ formError }}</p>

                <button type="submit" :disabled="saving">
                    {{ saving ? 'Publishing...' : 'Publish post' }}
                </button>
            </form>
        </section>

        <section class="feed">
            <h2>Latest Posts</h2>

            <p v-if="loading">Loading posts...</p>
            <p v-else-if="error" class="error">{{ error }}</p>
            <p v-else-if="posts.length === 0">
                No posts yet. Be the first to post!
            </p>

            <article
                v-for="post in posts"
                :key="post.id"
                class="post"
            >
                <div class="avatar">
                    {{ getInitials(post.author_name) }}
                </div>

                <div class="post-content">
                    <h3>
                        {{ post.author_name }}
                        <span>{{ post.username }}</span>
                    </h3>

                    
                    <div v-if="editingPostId === post.id" class="edit-form">
                        <textarea
                            v-model="editContent"
                            maxlength="5000"
                            rows="4"
                            required
                        ></textarea>

                        <p v-if="editError" class="error">{{ editError }}</p>

                        <div class="edit-actions">
                            <button
                                type="button"
                                @click="updatePost(post.id)"
                                :disabled="savingEdit"
                            >
                                {{ savingEdit ? 'Saving...' : 'Save changes' }}
                            </button>

                            <button
                                type="button"
                                class="cancel-button"
                                @click="cancelEdit"
                                :disabled="savingEdit"
                            >
                                Cancel
                            </button>
                            
                            

                        </div>
                    </div>

                    <div v-else>
                        <p>{{ post.content }}</p>

                        <button
                            type="button"
                            class="edit-button"
                            @click="startEdit(post)"
                        >
                            Edit
                        </button>

                        <button
                                type="button"
                                class="delete-button"
                                @click="deletePost(post.id)"
                                :disabled="deletingPostId === post.id"
                            >
                                {{ deletingPostId === post.id ? 'Deleting...' : 'Delete' }}
                            </button>
                    </div>

                    <small>
                        {{ formatDate(post.posted_at || post.created_at) }}
                    </small>

                </div>
            </article>
        </section>
    </main>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const posts = ref([]);
const loading = ref(true);
const error = ref('');

const authorName = ref('');
const username = ref('');
const content = ref('');

const saving = ref(false);
const formError = ref('');

const editingPostId = ref(null);
const editContent = ref('');
const savingEdit = ref(false);
const editError = ref('');

const deletingPostId = ref(null);

//searchbar
const query = ref('');

async function deletePost(postId) {
    const confirmed = window.confirm(
        'Are you sure you want to delete this post?'
    );

    if (!confirmed) {
        return;
    }

    deletingPostId.value = postId;

    try {
        const response = await fetch(`/api/posts/${postId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Could not delete the post.');
        }

        posts.value = posts.value.filter(post => post.id !== postId);
    } catch (err) {
        console.error(err);
        window.alert(err.message || 'Could not connect to Laravel.');
    } finally {
        deletingPostId.value = null;
    }
}

function startEdit(post) {
    editingPostId.value = post.id;
    editContent.value = post.content;
    editError.value = '';
}

function cancelEdit() {
    editingPostId.value = null;
    editContent.value = '';
    editError.value = '';
}

async function updatePost(postId) {
    editError.value = '';

    if (!editContent.value.trim()) {
        editError.value = 'Post content cannot be empty.';
        return;
    }

    savingEdit.value = true;

    try {
        const response = await fetch(`/api/posts/${postId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                content: editContent.value.trim(),
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                editError.value = Object.values(data.errors)
                    .flat()
                    .join(' ');
            } else {
                editError.value = data.message || 'Could not update the post.';
            }

            return;
        }

        const index = posts.value.findIndex(post => post.id === postId);

        if (index !== -1) {
            posts.value[index] = data;
        }

        cancelEdit();
    } catch (err) {
        console.error(err);
        editError.value = 'Could not connect to Laravel. Please try again.';
    } finally {
        savingEdit.value = false;
    }
}

async function loadPosts() {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/api/posts');

        if (!response.ok) {
            throw new Error('Failed to load posts.');
        }

        posts.value = await response.json();
    } catch (err) {
        console.error(err);
        error.value = 'Could not load posts. Please try again.';
    } finally {
        loading.value = false;
    }
}

async function createPost() {
    formError.value = '';
    saving.value = true;

    try {
        const response = await fetch('/api/posts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                author_name: authorName.value.trim(),
                username: username.value.trim(),
                content: content.value.trim(),
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                formError.value = Object.values(data.errors)
                    .flat()
                    .join(' ');
            } else {
                formError.value = data.message || 'Could not publish the post.';
            }

            return;
        }

        // Add the saved post to the top of the feed.
        posts.value.unshift(data);

        // Clear the form after successful publishing.
        authorName.value = '';
        username.value = '';
        content.value = '';
    } catch (err) {
        console.error(err);
        formError.value = 'Could not connect to Laravel. Please try again.';
    } finally {
        saving.value = false;
    }
}

function getInitials(name) {
    if (!name) return '?';

    return name
        .split(' ')
        .map(part => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

function formatDate(dateString) {
    if (!dateString) return 'Date unavailable';

    return new Date(dateString).toLocaleString();
}

onMounted(loadPosts);
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

.composer {
    margin-top: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.composer form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.composer label {
    font-size: 14px;
    font-weight: bold;
}

.composer input,
.composer textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font: inherit;
}

.composer textarea {
    resize: vertical;
}

.counter {
    margin: 0;
    text-align: right;
    font-size: 12px;
    color: #657786;
}

.composer button {
    align-self: flex-start;
    padding: 10px 18px;
    border: none;
    border-radius: 20px;
    background: #1d9bf0;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

.composer button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
    white-space: pre-wrap;
    overflow-wrap: anywhere;
}

.post-content small {
    font-size: 12px;
}

.error {
    color: #dc2626;
}


.edit-form {
    width: 100%;
}

.edit-form textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font: inherit;
    resize: vertical;
}

.edit-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.edit-button,
.edit-actions button {
    padding: 7px 14px;
    border: none;
    border-radius: 18px;
    background: #1d9bf0;
    color: white;
    cursor: pointer;
}

.edit-button {
    margin-bottom: 10px;
}

.edit-actions .cancel-button {
    background: #e5e7eb;
    color: #17202a;
}

.edit-actions button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


.delete-button {
    margin-left: 8px;
    padding: 7px 14px;
    border: none;
    border-radius: 18px;
    background: #fee2e2;
    color: #b91c1c;
    cursor: pointer;
}

.delete-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


</style>
