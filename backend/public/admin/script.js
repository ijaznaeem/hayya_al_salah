// Admin Panel JavaScript
class AdminPanel {
    constructor() {
        this.baseURL = '/api';
        this.token = localStorage.getItem('admin_token');
        this.currentUser = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuth();
    }

    setupEventListeners() {
        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.login();
        });

        // Logout button
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const section = item.dataset.section;
                this.showSection(section);
            });
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.refreshCurrentSection();
        });

        // Modal close buttons
        document.querySelectorAll('[data-modal]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modalId = btn.dataset.modal;
                this.closeModal(modalId);
            });
        });

        // Add buttons
        document.getElementById('addTopicBtn').addEventListener('click', () => {
            this.showTopicModal();
        });

        document.getElementById('addLectureBtn').addEventListener('click', () => {
            this.showLectureModal();
        });

        // Forms
        document.getElementById('topicForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveTopic();
        });

        document.getElementById('lectureForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveLecture();
        });

        // Search and filters
        document.getElementById('searchLectures').addEventListener('input', (e) => {
            this.searchLectures(e.target.value);
        });

        document.getElementById('topicFilter').addEventListener('change', (e) => {
            this.filterLecturesByTopic(e.target.value);
        });

        // File upload functionality
        this.setupFileUploads();
    }

    setupFileUploads() {
        // Upload button handlers
        document.querySelectorAll('.upload-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const type = btn.dataset.type;
                const target = btn.dataset.target;
                
                this.openUploadModal(type, target);
            });
        });

        // File input change handlers (keep existing for backward compatibility)
        document.getElementById('videoUpload').addEventListener('change', (e) => {
            this.handleFileUpload(e.target, 'video', 'lectureVideoUrl');
        });

        document.getElementById('thumbnailUpload').addEventListener('change', (e) => {
            this.handleFileUpload(e.target, 'image', 'lectureThumbnailUrl');
        });

        document.getElementById('documentUpload').addEventListener('change', (e) => {
            this.handleFileUpload(e.target, 'document', 'lectureReferenceUrl');
        });

        document.getElementById('topicImageUpload').addEventListener('change', (e) => {
            this.handleFileUpload(e.target, 'image', 'topicImageUrl');
        });

        // Upload modal handlers
        this.setupUploadModal();
    }

    async handleFileUpload(fileInput, type, targetInputId) {
        const file = fileInput.files[0];
        if (!file) return;

        const targetInput = document.getElementById(targetInputId);
        const uploadBtn = document.querySelector(`[data-target="${targetInputId}"]`);
        
        try {
            // Show loading state
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            
            // Create form data
            const formData = new FormData();
            formData.append('file', file);

            // Upload file
            const response = await fetch(`/api/upload/${type}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Set the URL in the target input
                targetInput.value = result.url;
                this.showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} uploaded successfully!`, 'success');
                
                // Show preview if it's an image
                if (type === 'image') {
                    this.showImagePreview(targetInput, result.url);
                }
            } else {
                this.showToast(result.error || 'Upload failed', 'error');
            }

        } catch (error) {
            console.error('Upload error:', error);
            this.showToast('Upload failed. Please try again.', 'error');
        } finally {
            // Reset button state
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = `<i class="fas fa-upload"></i> Upload ${type.charAt(0).toUpperCase() + type.slice(1)}`;
            
            // Clear file input
            fileInput.value = '';
        }
    }

    showImagePreview(input, url) {
        // Remove existing preview
        const existingPreview = input.parentNode.querySelector('.file-preview');
        if (existingPreview) {
            existingPreview.remove();
        }

        // Create new preview
        const preview = document.createElement('div');
        preview.className = 'file-preview';
        preview.innerHTML = `
            <img src="${url}" alt="Preview" style="max-width: 200px; max-height: 100px; object-fit: cover; border-radius: 4px;">
            <div style="margin-top: 8px; font-size: 0.875rem; color: var(--text-secondary);">
                Preview of uploaded image
            </div>
        `;
        
        input.parentNode.appendChild(preview);
    }

    setupUploadModal() {
        const modal = document.getElementById('uploadModal');
        const selectBtn = modal.querySelector('.upload-select');
        const confirmBtn = modal.querySelector('.upload-confirm');
        const doneBtn = modal.querySelector('.upload-done');
        const cancelBtn = modal.querySelector('.upload-cancel');

        // Hidden file input for upload modal
        if (!document.getElementById('modalFileInput')) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.id = 'modalFileInput';
            fileInput.style.display = 'none';
            document.body.appendChild(fileInput);
        }

        const fileInput = document.getElementById('modalFileInput');

        selectBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.displaySelectedFile(file);
            }
        });

        confirmBtn.addEventListener('click', () => {
            this.confirmUpload();
        });

        // Reset modal when closed
        modal.addEventListener('click', (e) => {
            if (e.target.dataset.modal === 'uploadModal') {
                this.resetUploadModal();
            }
        });

        cancelBtn.addEventListener('click', () => {
            this.resetUploadModal();
        });
    }

    openUploadModal(type, target) {
        this.uploadType = type;
        this.uploadTarget = target;
        
        const modal = document.getElementById('uploadModal');
        const fileInput = document.getElementById('modalFileInput');
        
        // Set file input accept attribute based on type
        if (type === 'video') {
            fileInput.accept = 'video/*';
        } else if (type === 'image') {
            fileInput.accept = 'image/*';
        } else if (type === 'document') {
            fileInput.accept = '.pdf,.doc,.docx';
        }

        // Update modal title
        const title = modal.querySelector('.modal-header h3');
        title.textContent = `Upload ${type.charAt(0).toUpperCase() + type.slice(1)}`;

        this.resetUploadModal();
        modal.classList.add('active');
    }

    displaySelectedFile(file) {
        const modal = document.getElementById('uploadModal');
        const fileInfo = modal.querySelector('.upload-file-info');
        const icon = fileInfo.querySelector('.upload-icon');
        const filename = fileInfo.querySelector('.upload-filename');
        const filesize = fileInfo.querySelector('.upload-filesize');
        const selectBtn = modal.querySelector('.upload-select');
        const confirmBtn = modal.querySelector('.upload-confirm');

        // Update file icon based on type
        icon.className = 'fas upload-icon';
        if (file.type.startsWith('video/')) {
            icon.className += ' fa-file-video';
        } else if (file.type.startsWith('image/')) {
            icon.className += ' fa-file-image';
        } else if (file.type === 'application/pdf') {
            icon.className += ' fa-file-pdf';
        } else if (file.type.includes('word')) {
            icon.className += ' fa-file-word';
        } else {
            icon.className += ' fa-file';
        }

        // Update file info
        filename.textContent = file.name;
        filesize.textContent = this.formatFileSize(file.size);
        
        // Update UI state
        fileInfo.classList.add('has-file');
        selectBtn.style.display = 'none';
        confirmBtn.style.display = 'inline-flex';

        this.selectedFile = file;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async confirmUpload() {
        if (!this.selectedFile) return;

        const modal = document.getElementById('uploadModal');
        const progressContainer = modal.querySelector('.upload-progress-container');
        const resultContainer = modal.querySelector('.upload-result');
        const errorContainer = modal.querySelector('.upload-error');
        const confirmBtn = modal.querySelector('.upload-confirm');
        const doneBtn = modal.querySelector('.upload-done');
        const progressFill = modal.querySelector('.progress-fill');
        const percentageSpan = modal.querySelector('.upload-percentage');
        const speedSpan = modal.querySelector('.upload-speed');
        const statusSpan = modal.querySelector('.upload-status');

        try {
            // Reset and show progress
            progressContainer.style.display = 'block';
            resultContainer.style.display = 'none';
            errorContainer.style.display = 'none';
            confirmBtn.style.display = 'none';
            
            progressFill.style.width = '0%';
            percentageSpan.textContent = '0%';
            statusSpan.textContent = 'Uploading...';
            speedSpan.textContent = '';

            // Create form data
            const formData = new FormData();
            formData.append('file', this.selectedFile);

            // Track upload progress
            let startTime = Date.now();
            let lastLoaded = 0;

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    const elapsed = (Date.now() - startTime) / 1000;
                    const speed = e.loaded / elapsed;
                    const remaining = (e.total - e.loaded) / speed;

                    progressFill.style.width = percentComplete + '%';
                    percentageSpan.textContent = Math.round(percentComplete) + '%';
                    
                    if (elapsed > 1) {
                        speedSpan.textContent = `${this.formatFileSize(speed)}/s - ${Math.round(remaining)}s remaining`;
                    }
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    const result = JSON.parse(xhr.responseText);
                    if (result.success) {
                        this.handleUploadSuccess(result);
                    } else {
                        this.handleUploadError(result.error || 'Upload failed');
                    }
                } else {
                    this.handleUploadError('Upload failed. Please try again.');
                }
            });

            xhr.addEventListener('error', () => {
                this.handleUploadError('Network error. Please try again.');
            });

            xhr.open('POST', `${this.baseURL}/upload/${this.uploadType}`);
            xhr.setRequestHeader('Authorization', `Bearer ${this.token}`);
            xhr.send(formData);

        } catch (error) {
            console.error('Upload error:', error);
            this.handleUploadError('Upload failed. Please try again.');
        }
    }

    handleUploadSuccess(result) {
        const modal = document.getElementById('uploadModal');
        const progressContainer = modal.querySelector('.upload-progress-container');
        const resultContainer = modal.querySelector('.upload-result');
        const preview = modal.querySelector('.upload-preview');
        const doneBtn = modal.querySelector('.upload-done');
        const statusSpan = modal.querySelector('.upload-status');

        // Hide progress, show result
        progressContainer.style.display = 'none';
        resultContainer.style.display = 'block';
        doneBtn.style.display = 'inline-flex';
        statusSpan.textContent = 'Upload complete!';

        // Set the URL in the target input
        const targetInput = document.getElementById(this.uploadTarget);
        if (targetInput) {
            console.log('Setting upload URL:', result.url, 'to input:', this.uploadTarget);
            targetInput.value = result.url;
            
            // Trigger change event to notify any listeners
            targetInput.dispatchEvent(new Event('change'));
            
            // Also show preview in form if it's an image (for thumbnail and topic image)
            if (this.uploadType === 'image' && (this.uploadTarget === 'lectureThumbnailUrl' || this.uploadTarget === 'topicImageUrl')) {
                this.showImagePreview(targetInput, result.url);
            }
        } else {
            console.error('Target input not found:', this.uploadTarget);
        }

        // Show preview
        if (this.uploadType === 'image') {
            preview.innerHTML = `
                <img src="${result.url}" alt="Uploaded image">
                <div>Image uploaded successfully</div>
            `;
        } else if (this.uploadType === 'video') {
            preview.innerHTML = `
                <video controls>
                    <source src="${result.url}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div>Video uploaded successfully</div>
            `;
        } else {
            preview.innerHTML = `
                <div class="file-info">
                    <i class="fas fa-file"></i>
                    <div>
                        <div><strong>${result.original_name}</strong></div>
                        <div>${this.formatFileSize(result.size)}</div>
                        <div>Document uploaded successfully</div>
                    </div>
                </div>
            `;
        }

        this.showToast(`${this.uploadType.charAt(0).toUpperCase() + this.uploadType.slice(1)} uploaded successfully!`, 'success');
    }

    handleUploadError(error) {
        const modal = document.getElementById('uploadModal');
        const progressContainer = modal.querySelector('.upload-progress-container');
        const errorContainer = modal.querySelector('.upload-error');
        const errorMessage = errorContainer.querySelector('.error-message');
        const confirmBtn = modal.querySelector('.upload-confirm');

        progressContainer.style.display = 'none';
        errorContainer.style.display = 'block';
        confirmBtn.style.display = 'inline-flex';
        errorMessage.textContent = error;

        this.showToast(error, 'error');
    }

    resetUploadModal() {
        const modal = document.getElementById('uploadModal');
        const fileInfo = modal.querySelector('.upload-file-info');
        const filename = modal.querySelector('.upload-filename');
        const filesize = modal.querySelector('.upload-filesize');
        const progressContainer = modal.querySelector('.upload-progress-container');
        const resultContainer = modal.querySelector('.upload-result');
        const errorContainer = modal.querySelector('.upload-error');
        const selectBtn = modal.querySelector('.upload-select');
        const confirmBtn = modal.querySelector('.upload-confirm');
        const doneBtn = modal.querySelector('.upload-done');
        const fileInput = document.getElementById('modalFileInput');

        // Reset file info
        fileInfo.classList.remove('has-file');
        filename.textContent = 'Select a file to upload';
        filesize.textContent = '';

        // Reset containers
        progressContainer.style.display = 'none';
        resultContainer.style.display = 'none';
        errorContainer.style.display = 'none';

        // Reset buttons
        selectBtn.style.display = 'inline-flex';
        confirmBtn.style.display = 'none';
        doneBtn.style.display = 'none';

        // Clear file input
        if (fileInput) {
            fileInput.value = '';
        }

        this.selectedFile = null;
        this.closeModal('uploadModal');
    }

    async checkAuth() {
        if (!this.token) {
            this.showLogin();
            return;
        }

        try {
            const response = await this.apiCall('/auth/profile', 'GET');
            if (response.user) {
                this.currentUser = response.user;
                if (this.currentUser.role !== 'admin') {
                    this.showToast('Access denied. Admin privileges required.', 'error');
                    this.logout();
                    return;
                }
                this.showAdminPanel();
                this.loadDashboard();
            } else {
                this.showLogin();
            }
        } catch (error) {
            this.showLogin();
        }
    }

    async login() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {
            this.showLoading(true);
            const response = await fetch(`${this.baseURL}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok && data.token) {
                if (data.user.role !== 'admin') {
                    this.showError('Access denied. Admin privileges required.');
                    return;
                }

                this.token = data.token;
                this.currentUser = data.user;
                localStorage.setItem('admin_token', this.token);
                
                this.showAdminPanel();
                this.loadDashboard();
                this.showToast('Login successful!', 'success');
            } else {
                this.showError(data.error || 'Login failed');
            }
        } catch (error) {
            this.showError('Network error. Please try again.');
        } finally {
            this.showLoading(false);
        }
    }

    logout() {
        this.token = null;
        this.currentUser = null;
        localStorage.removeItem('admin_token');
        this.showLogin();
        this.showToast('Logged out successfully', 'info');
    }

    showLogin() {
        document.getElementById('loginModal').classList.add('active');
        document.getElementById('adminPanel').classList.remove('active');
        document.getElementById('loginError').classList.remove('show');
    }

    showAdminPanel() {
        document.getElementById('loginModal').classList.remove('active');
        document.getElementById('adminPanel').classList.add('active');
        
        if (this.currentUser) {
            document.getElementById('adminUsername').textContent = this.currentUser.username;
        }
    }

    showSection(sectionName) {
        // Update navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');

        // Update content
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${sectionName}-section`).classList.add('active');

        // Update page title
        const titles = {
            dashboard: 'Dashboard',
            users: 'User Management',
            topics: 'Topics Management',
            lectures: 'Lectures Management',
            analytics: 'Analytics & Reports'
        };
        document.getElementById('pageTitle').textContent = titles[sectionName];

        // Load section data
        this.loadSectionData(sectionName);
    }

    async loadSectionData(section) {
        switch (section) {
            case 'dashboard':
                await this.loadDashboard();
                break;
            case 'users':
                await this.loadUsers();
                break;
            case 'topics':
                await this.loadTopics();
                break;
            case 'lectures':
                await this.loadLectures();
                await this.loadTopics(); // Load topics for the dropdown
                break;
            case 'analytics':
                await this.loadAnalytics();
                break;
        }
    }

    async loadDashboard() {
        try {
            this.showLoading(true);
            
            // Load statistics
            const [topics, lectures, users] = await Promise.all([
                this.apiCall('/topics'),
                this.apiCall('/lectures'),
                this.apiCall('/users') // We'll need to add this endpoint
            ]);

            document.getElementById('totalTopics').textContent = topics.count || topics.data?.length || 0;
            document.getElementById('totalLectures').textContent = lectures.pagination?.total || lectures.data?.length || 0;
            document.getElementById('totalUsers').textContent = users?.count || 0;

            // Load recent data
            await this.loadRecentUsers();
            await this.loadPopularLectures();

        } catch (error) {
            console.error('Error loading dashboard:', error);
        } finally {
            this.showLoading(false);
        }
    }

    async loadRecentUsers() {
        try {
            // This would need a backend endpoint for recent users
            const recentUsersContainer = document.getElementById('recentUsers');
            recentUsersContainer.innerHTML = '<p class="text-secondary">Recent users data not available</p>';
        } catch (error) {
            console.error('Error loading recent users:', error);
        }
    }

    async loadPopularLectures() {
        try {
            const response = await this.apiCall('/lectures/popular?limit=5');
            const container = document.getElementById('popularLectures');
            
            if (response.data && response.data.length > 0) {
                container.innerHTML = response.data.map(lecture => `
                    <div class="recent-item">
                        <div class="recent-item-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="recent-item-content">
                            <div class="recent-item-title">${lecture.title}</div>
                            <div class="recent-item-meta">${lecture.topic_title || 'Unknown Topic'} • ${lecture.duration}</div>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary">No popular lectures found</p>';
            }
        } catch (error) {
            console.error('Error loading popular lectures:', error);
        }
    }

    async loadUsers() {
        try {
            this.showLoading(true);
            // Note: We'll need to add a users endpoint to the backend
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">User management endpoint not implemented yet</td></tr>';
        } catch (error) {
            console.error('Error loading users:', error);
        } finally {
            this.showLoading(false);
        }
    }

    async loadTopics() {
        try {
            this.showLoading(true);
            const response = await this.apiCall('/topics');
            const tbody = document.getElementById('topicsTableBody');
            
            if (response.data && response.data.length > 0) {
                tbody.innerHTML = response.data.map(topic => `
                    <tr>
                        <td>${topic.id}</td>
                        <td>${topic.title}</td>
                        <td>${topic.description.substring(0, 50)}...</td>
                        <td>${topic.lessons_count || 0}</td>
                        <td>${new Date(topic.created_at).toLocaleDateString()}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning" onclick="adminPanel.editTopic(${topic.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminPanel.deleteTopic(${topic.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No topics found</td></tr>';
            }

            // Update topic filter dropdown
            this.updateTopicFilter(response.data || []);
        } catch (error) {
            console.error('Error loading topics:', error);
            // Still try to update with empty array to clear dropdowns
            this.updateTopicFilter([]);
        } finally {
            this.showLoading(false);
        }
    }

    async loadLectures() {
        try {
            this.showLoading(true);
            const response = await this.apiCall('/lectures');
            const tbody = document.getElementById('lecturesTableBody');
            
            if (response.data && response.data.length > 0) {
                tbody.innerHTML = response.data.map(lecture => `
                    <tr>
                        <td>${lecture.id}</td>
                        <td>${lecture.title}</td>
                        <td>${lecture.topic_title || 'Unknown'}</td>
                        <td>${lecture.duration}</td>
                        <td>${lecture.lesson_number}</td>
                        <td>${new Date(lecture.created_at).toLocaleDateString()}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning" onclick="adminPanel.editLecture(${lecture.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminPanel.deleteLecture(${lecture.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No lectures found</td></tr>';
            }
        } catch (error) {
            console.error('Error loading lectures:', error);
        } finally {
            this.showLoading(false);
        }
    }

    async loadAnalytics() {
        try {
            this.showLoading(true);
            
            // Load most favorited lectures
            const mostFavorited = await this.apiCall('/stats/most-favorited?limit=10');
            const container = document.getElementById('mostFavorited');
            
            if (mostFavorited.data && mostFavorited.data.length > 0) {
                container.innerHTML = mostFavorited.data.map(lecture => `
                    <div class="analytics-item">
                        <div class="analytics-item-info">
                            <div class="analytics-item-title">${lecture.title}</div>
                            <div class="analytics-item-meta">${lecture.topic_title}</div>
                        </div>
                        <div class="analytics-item-value">${lecture.favorite_count}</div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-secondary">No analytics data available</p>';
            }

            // User activity placeholder
            document.getElementById('userActivity').innerHTML = '<p class="text-secondary">User activity tracking not implemented yet</p>';
            
        } catch (error) {
            console.error('Error loading analytics:', error);
        } finally {
            this.showLoading(false);
        }
    }

    updateTopicFilter(topics) {
        const select = document.getElementById('topicFilter');
        const lectureTopicSelect = document.getElementById('lectureTopicId');
        
        if (!select || !lectureTopicSelect) {
            console.error('Topic select elements not found');
            return;
        }
        
        const options = topics.map(topic => 
            `<option value="${topic.id}">${topic.title}</option>`
        ).join('');
        
        select.innerHTML = '<option value="">All Topics</option>' + options;
        lectureTopicSelect.innerHTML = '<option value="">Select Topic</option>' + options;
    }

    // Topic Management
    showTopicModal(topicId = null) {
        const modal = document.getElementById('topicModal');
        const form = document.getElementById('topicForm');
        const title = document.getElementById('topicModalTitle');
        
        form.reset();
        document.getElementById('topicId').value = '';
        
        if (topicId) {
            title.textContent = 'Edit Topic';
            this.loadTopicForEdit(topicId);
        } else {
            title.textContent = 'Add Topic';
        }
        
        modal.classList.add('active');
    }

    async loadTopicForEdit(topicId) {
        try {
            const response = await this.apiCall(`/topics/${topicId}`);
            const topic = response.data;
            
            document.getElementById('topicId').value = topic.id;
            document.getElementById('topicTitle').value = topic.title;
            document.getElementById('topicDescription').value = topic.description;
            document.getElementById('topicImageUrl').value = topic.image_url || '';
        } catch (error) {
            this.showToast('Error loading topic data', 'error');
        }
    }

    async saveTopic() {
        const formData = new FormData(document.getElementById('topicForm'));
        const topicId = document.getElementById('topicId').value;
        const data = Object.fromEntries(formData);
        
        try {
            this.showLoading(true);
            
            let response;
            if (topicId) {
                response = await this.apiCall(`/topics/${topicId}`, 'PUT', data);
            } else {
                response = await this.apiCall('/topics', 'POST', data);
            }
            
            this.closeModal('topicModal');
            this.loadTopics();
            this.showToast(`Topic ${topicId ? 'updated' : 'created'} successfully!`, 'success');
        } catch (error) {
            this.showToast(error.message || 'Error saving topic', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async editTopic(topicId) {
        this.showTopicModal(topicId);
    }

    async deleteTopic(topicId) {
        if (!confirm('Are you sure you want to delete this topic? This will also delete all associated lectures.')) {
            return;
        }
        
        try {
            this.showLoading(true);
            await this.apiCall(`/topics/${topicId}`, 'DELETE');
            this.loadTopics();
            this.showToast('Topic deleted successfully!', 'success');
        } catch (error) {
            this.showToast(error.message || 'Error deleting topic', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    // Lecture Management
    async showLectureModal(lectureId = null) {
        const modal = document.getElementById('lectureModal');
        const form = document.getElementById('lectureForm');
        const title = document.getElementById('lectureModalTitle');
        
        form.reset();
        document.getElementById('lectureId').value = '';
        
        // Load topics first to populate the dropdown
        await this.loadTopicsForModal();
        
        if (lectureId) {
            title.textContent = 'Edit Lecture';
            this.loadLectureForEdit(lectureId);
        } else {
            title.textContent = 'Add Lecture';
        }
        
        modal.classList.add('active');
    }

    async loadLectureForEdit(lectureId) {
        try {
            const response = await this.apiCall(`/lectures/${lectureId}`);
            const lecture = response.data;
            
            document.getElementById('lectureId').value = lecture.id;
            document.getElementById('lectureTopicId').value = lecture.topic_id;
            document.getElementById('lectureTitle').value = lecture.title;
            document.getElementById('lectureDescription').value = lecture.description;
            document.getElementById('lectureVideoUrl').value = lecture.video_url;
            document.getElementById('lectureDuration').value = lecture.duration;
            document.getElementById('lectureLessonNumber').value = lecture.lesson_number;
            document.getElementById('lectureThumbnailUrl').value = lecture.thumbnail_url || '';
            document.getElementById('lectureReferenceUrl').value = lecture.reference_file_url || '';
        } catch (error) {
            this.showToast('Error loading lecture data', 'error');
        }
    }

    async saveLecture() {
        const formData = new FormData(document.getElementById('lectureForm'));
        const lectureId = document.getElementById('lectureId').value;
        const data = Object.fromEntries(formData);
        
        try {
            this.showLoading(true);
            
            let response;
            if (lectureId) {
                response = await this.apiCall(`/lectures/${lectureId}`, 'PUT', data);
            } else {
                response = await this.apiCall('/lectures', 'POST', data);
            }
            
            this.closeModal('lectureModal');
            this.loadLectures();
            this.showToast(`Lecture ${lectureId ? 'updated' : 'created'} successfully!`, 'success');
        } catch (error) {
            this.showToast(error.message || 'Error saving lecture', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    async editLecture(lectureId) {
        this.showLectureModal(lectureId);
    }

    async deleteLecture(lectureId) {
        if (!confirm('Are you sure you want to delete this lecture?')) {
            return;
        }
        
        try {
            this.showLoading(true);
            await this.apiCall(`/lectures/${lectureId}`, 'DELETE');
            this.loadLectures();
            this.showToast('Lecture deleted successfully!', 'success');
        } catch (error) {
            this.showToast(error.message || 'Error deleting lecture', 'error');
        } finally {
            this.showLoading(false);
        }
    }

    // Search and Filter
    async searchLectures(query) {
        if (query.length < 2) {
            this.loadLectures();
            return;
        }
        
        try {
            const response = await this.apiCall(`/lectures?search=${encodeURIComponent(query)}`);
            const tbody = document.getElementById('lecturesTableBody');
            
            if (response.data && response.data.length > 0) {
                tbody.innerHTML = response.data.map(lecture => `
                    <tr>
                        <td>${lecture.id}</td>
                        <td>${lecture.title}</td>
                        <td>${lecture.topic_title || 'Unknown'}</td>
                        <td>${lecture.duration}</td>
                        <td>${lecture.lesson_number}</td>
                        <td>${new Date(lecture.created_at).toLocaleDateString()}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning" onclick="adminPanel.editLecture(${lecture.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminPanel.deleteLecture(${lecture.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No lectures found</td></tr>';
            }
        } catch (error) {
            console.error('Error searching lectures:', error);
        }
    }

    async filterLecturesByTopic(topicId) {
        if (!topicId) {
            this.loadLectures();
            return;
        }
        
        try {
            const response = await this.apiCall(`/topics/${topicId}/lectures`);
            const tbody = document.getElementById('lecturesTableBody');
            
            if (response.data && response.data.length > 0) {
                tbody.innerHTML = response.data.map(lecture => `
                    <tr>
                        <td>${lecture.id}</td>
                        <td>${lecture.title}</td>
                        <td>${lecture.topic_title || 'Unknown'}</td>
                        <td>${lecture.duration}</td>
                        <td>${lecture.lesson_number}</td>
                        <td>${new Date(lecture.created_at).toLocaleDateString()}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-warning" onclick="adminPanel.editLecture(${lecture.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminPanel.deleteLecture(${lecture.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No lectures found for this topic</td></tr>';
            }
        } catch (error) {
            console.error('Error filtering lectures:', error);
        }
    }

    // Utility Methods
    async apiCall(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (this.token) {
            options.headers.Authorization = `Bearer ${this.token}`;
        }

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`${this.baseURL}${endpoint}`, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'API request failed');
        }

        return result;
    }

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    showLoading(show) {
        const spinner = document.getElementById('loadingSpinner');
        if (show) {
            spinner.classList.add('active');
        } else {
            spinner.classList.remove('active');
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('loginError');
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
    }

    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => container.removeChild(toast), 300);
        }, 5000);
    }

    refreshCurrentSection() {
        const activeSection = document.querySelector('.nav-item.active').dataset.section;
        this.loadSectionData(activeSection);
        this.showToast('Data refreshed', 'success');
    }

    async loadTopicsForModal() {
        try {
            const response = await this.apiCall('/topics', 'GET');
            const topics = response.data || response;
            
            const select = document.getElementById('lectureTopicId');
            if (!select) {
                console.error('lectureTopicId select element not found');
                return;
            }
            
            select.innerHTML = '<option value="">Select Topic</option>';
            
            if (Array.isArray(topics)) {
                topics.forEach(topic => {
                    const option = document.createElement('option');
                    option.value = topic.id;
                    option.textContent = topic.title;
                    select.appendChild(option);
                });
            } else {
                console.error('Topics is not an array:', topics);
            }
        } catch (error) {
            console.error('Failed to load topics for modal:', error);
            this.showToast('Failed to load topics', 'error');
        }
    }
}

// Initialize admin panel when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.adminPanel = new AdminPanel();
});

// Close modals when clicking outside
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});
