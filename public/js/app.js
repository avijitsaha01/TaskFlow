// Modal handling
function openModal(taskId = null) {
    const modal = document.getElementById('modalBack');
    const form = document.getElementById('taskForm');
    const title = document.getElementById('modalTitle');

    if (taskId) {
        // Edit mode - populate form from data attributes
        const taskEl = document.querySelector(`[data-task-id="${taskId}"]`);
        if (taskEl) {
            title.textContent = 'Edit Task';
            form.action = `/tasks/${taskId}`;
            document.getElementById('methodField').value = 'PUT';
            document.getElementById('taskId').value = taskId;
            document.getElementById('taskTitle').value = taskEl.dataset.title || '';
            document.getElementById('taskDesc').value = taskEl.dataset.description || '';
            document.getElementById('taskPriority').value = taskEl.dataset.priority || 'medium';
            document.getElementById('taskDue').value = taskEl.dataset.due || '';
        }
    } else {
        // Create mode
        title.textContent = 'Add Task';
        form.action = '/tasks';
        document.getElementById('methodField').value = 'POST';
        document.getElementById('taskId').value = '';
        form.reset();
        document.getElementById('taskPriority').value = 'medium';
    }

    modal.classList.add('show');
    document.getElementById('taskTitle').focus();
}

function closeModal() {
    document.getElementById('modalBack').classList.remove('show');
    // Clear validation errors
    document.querySelectorAll('.error-text').forEach(el => el.remove());
}

// Toast notification
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

// Delete confirmation
function confirmDelete(taskId) {
    if (confirm('Delete this task?')) {
        document.getElementById('delete-form-' + taskId).submit();
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add button
    const addBtn = document.getElementById('addBtn');
    if (addBtn) addBtn.addEventListener('click', () => openModal());

    // Close modal
    const closeBtn = document.getElementById('closeModal');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    // Click outside modal
    const modalBack = document.getElementById('modalBack');
    if (modalBack) {
        modalBack.addEventListener('click', function(e) {
            if (e.target === modalBack) closeModal();
        });
    }

    // Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Show flash message toast
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage && flashMessage.value) {
        showToast(flashMessage.value);
    }

    // Auto-submit filters on change
    const priorityFilter = document.getElementById('priorityFilter');
    const sortFilter = document.getElementById('sort');

    if (priorityFilter) {
        priorityFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }

    // Search with debounce
    const searchInput = document.getElementById('search');
    let searchTimer;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 400);
        });
    }

    // Reopen the modal after a failed submission, keeping old input intact
    if (modalBack && modalBack.dataset.reopen === '1') {
        modalBack.classList.add('show');
    }

    // Keyboard shortcuts: "n" adds a task, "/" focuses search
    document.addEventListener('keydown', function(e) {
        const target = e.target;
        const typing = target instanceof HTMLElement && (
            target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.tagName === 'SELECT' ||
            target.isContentEditable
        );

        if (typing || e.metaKey || e.ctrlKey || e.altKey) return;

        if (e.key === 'n' || e.key === 'N') {
            e.preventDefault();
            openModal();
        }

        if (e.key === '/') {
            const search = document.getElementById('search');
            if (search) {
                e.preventDefault();
                search.focus();
            }
        }
    });
});
