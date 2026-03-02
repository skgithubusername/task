// Notes App JavaScript
let editingNoteId = null;
let originalNoteData = {};

// DOM Elements
const noteForm = document.getElementById('noteForm');
const titleInput = document.getElementById('title');
const descriptionInput = document.getElementById('description');
const summaryInput = document.getElementById('summary');
const submitBtn = document.getElementById('submitBtn');
const cancelBtn = document.getElementById('cancelBtn');
const notesContainer = document.getElementById('notesContainer');

// Initialize
document.addEventListener('DOMContentLoaded', loadNotes);

// Form Submit
noteForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Validate
    if (!validateForm()) {
        return;
    }
    
    const noteData = {
        title: titleInput.value.trim(),
        description: descriptionInput.value.trim(),
        summary: summaryInput.value.trim()
    };
    
    try {
        if (editingNoteId) {
            // Check if data actually changed
            if (isDataUnchanged(noteData)) {
                showToast('No changes detected', 'info');
                resetForm();
                return;
            }
            // Update existing note
            await updateNote(editingNoteId, noteData);
        } else {
            // Create new note
            await createNote(noteData);
        }
        
        // Reset form
        resetForm();
    } catch (error) {
        console.error('Error saving note:', error);
        showToast('Error saving note. Please try again.', 'error');
    }
});

// Validate Form
function validateForm() {
    let isValid = true;
    
    // Clear previous errors
    document.getElementById('titleError').textContent = '';
    document.getElementById('descriptionError').textContent = '';
    
    // Validate Title
    if (!titleInput.value.trim()) {
        document.getElementById('titleError').textContent = 'Title is required';
        isValid = false;
    }
    
    // Validate Description
    if (!descriptionInput.value.trim()) {
        document.getElementById('descriptionError').textContent = 'Description is required';
        isValid = false;
    }
    
    return isValid;
}

// Check if data is unchanged
function isDataUnchanged(noteData) {
    return (
        noteData.title === originalNoteData.title &&
        noteData.description === originalNoteData.description &&
        noteData.summary === originalNoteData.summary
    );
}

// Create Note
async function createNote(noteData) {
    const response = await fetch('api/create_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(noteData)
    });
    
    const result = await response.json();
    
    if (result.success) {
        showToast('Note created successfully!', 'success');
        loadNotes();
    } else {
        throw new Error(result.message || 'Failed to create note');
    }
}

// Load Notes
async function loadNotes() {
    try {
        notesContainer.innerHTML = '<div class="loading">Loading notes...</div>';
        
        const response = await fetch('api/get_notes.php');
        const result = await response.json();
        
        if (result.success) {
            displayNotes(result.notes);
        } else {
            notesContainer.innerHTML = '<div class="no-notes">No notes found</div>';
        }
    } catch (error) {
        console.error('Error loading notes:', error);
        notesContainer.innerHTML = '<div class="no-notes">Error loading notes</div>';
    }
}

// Display Notes
function displayNotes(notes) {
    if (notes.length === 0) {
        notesContainer.innerHTML = '<div class="no-notes">No notes found. Add your first note above!</div>';
        return;
    }
    
    notesContainer.innerHTML = notes.map(note => `
        <div class="note-card" data-note-id="${note.id}">
            <div class="note-header">
                <h3 class="note-title">${escapeHtml(note.title)}</h3>
                <div class="note-actions">
                    <button class="summarize-btn" onclick="summarizeNote(${note.id})">🤖 AI Summarise</button>
                    <button class="edit-btn" onclick="editNote(${note.id})">✏️ Edit</button>
                    <button class="delete-btn" onclick="deleteNote(${note.id})">🗑️ Delete</button>
                </div>
            </div>
            <p class="note-description">${escapeHtml(note.description)}</p>
            ${note.summary ? `<div class="note-summary">${escapeHtml(note.summary)}</div>` : ''}
        </div>
    `).join('');
}

// Edit Note
async function editNote(id) {
    try {
        const response = await fetch(`api/get_note.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const note = result.note;
            titleInput.value = note.title;
            descriptionInput.value = note.description;
            summaryInput.value = note.summary || '';
            
            // Store original data for comparison
            originalNoteData = {
                title: note.title,
                description: note.description,
                summary: note.summary || ''
            };
            
            editingNoteId = id;
            submitBtn.textContent = 'Update Note';
            cancelBtn.style.display = 'inline-block';
        }
    } catch (error) {
        console.error('Error loading note:', error);
        showToast('Error loading note', 'error');
    }
}

// Update Note
async function updateNote(id, noteData) {
    const response = await fetch(`api/update_note.php?id=${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(noteData)
    });
    
    const result = await response.json();
    
    if (result.success) {
        showToast('Note updated successfully!', 'success');
        loadNotes();
    } else {
        throw new Error(result.message || 'Failed to update note');
    }
}

// Delete Note
async function deleteNote(id) {
    if (!confirm('Are you sure you want to delete this note?')) {
        return;
    }
    
    try {
        const response = await fetch(`api/delete_note.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Note deleted successfully!', 'success');
            loadNotes();
        } else {
            showToast('Error deleting note', 'error');
        }
    } catch (error) {
        console.error('Error deleting note:', error);
        showToast('Error deleting note', 'error');
    }
}

// Summarize Note
async function summarizeNote(id) {
    const noteCard = document.querySelector(`[data-note-id="${id}"]`);
    const summarizeBtn = noteCard.querySelector('.summarize-btn');
    const originalText = summarizeBtn.textContent;
    
    summarizeBtn.textContent = '⏳ Summarizing...';
    summarizeBtn.disabled = true;
    
    try {
        // Call mock AI API
        const response = await fetch(`api/mock_ai_summary.php?id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            // Update the summary in the database
            await updateNoteSummary(id, result.summary);
            
            // Update the UI
            const summaryDiv = noteCard.querySelector('.note-summary');
            if (summaryDiv) {
                summaryDiv.textContent = result.summary;
            } else {
                const summaryDiv = document.createElement('div');
                summaryDiv.className = 'note-summary';
                summaryDiv.textContent = result.summary;
                noteCard.querySelector('.note-description').after(summaryDiv);
            }
            
            // Update the input field if editing
            if (editingNoteId === id) {
                summaryInput.value = result.summary;
            }
            
            showToast('AI summary generated!', 'success');
        } else {
            throw new Error(result.message || 'Failed to generate summary');
        }
    } catch (error) {
        console.error('Error summarizing note:', error);
        showToast(error.message || 'Error generating AI summary', 'error');
    } finally {
        summarizeBtn.textContent = originalText;
        summarizeBtn.disabled = false;
    }
}

// Update Note Summary
async function updateNoteSummary(id, summary) {
    const response = await fetch(`api/update_note.php?id=${id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ summary })
    });
    
    return await response.json();
}

// Reset Form
function resetForm() {
    noteForm.reset();
    editingNoteId = null;
    originalNoteData = {};
    submitBtn.textContent = 'Add Note';
    cancelBtn.style.display = 'none';
}

// Cancel Edit
cancelBtn.addEventListener('click', resetForm);

// Toast Notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    // Set icon based on type
    const icons = {
        success: '✅',
        error: '❌',
        info: 'ℹ️'
    };
    toast.innerHTML = `<span class="toast-icon">${icons[type]}</span> <span class="toast-message">${message}</span>`;
    
    document.body.appendChild(toast);
    
    // Trigger reflow
    void toast.offsetWidth;
    toast.classList.add('show');
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Global functions for onclick handlers
window.summarizeNote = summarizeNote;
window.editNote = editNote;
window.deleteNote = deleteNote;
