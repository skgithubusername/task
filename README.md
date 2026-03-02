# Notes CRUD + AI Summary Application

A simple Notes application with CRUD functionality and AI summary generation using Groq API with Llama model.

## Features

- **CRUD Operations**: Create, Read, Update, and Delete notes
- **AI Summary**: Generate AI-powered summaries using Groq API (Llama 4 Scout)
- **Form Validation**: Required field validation for title and description
- **Database Storage**: All data persisted in MySQL database
- **Responsive Design**: Clean, modern UI with gradient backgrounds
- **Toast Notifications**: Beautiful success/error notifications

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx) or PHP built-in server
- Groq API key (free at https://console.groq.com/)

## Installation

1. **Clone or copy the files** to your web server directory

2. **Set up the database**:
   ```bash
   mysql -u root -p < database.sql
   ```
   
   Or import `database.sql` via phpMyAdmin

3. **Configure database connection** (if needed):
   - Edit `api/config.php`
   - Update `$host`, `$dbname`, `$username`, `$password` as needed

4. **Configure Groq API Key**:
   - Get your free API key from https://console.groq.com/
   - Copy `.env.example` to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Edit `.env` and add your API key:
     ```
     GROQ_API_KEY=your_actual_groq_api_key_here
     ```

5. **Start the server**:
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or use XAMPP/WAMP/LAMP
   ```

6. **Open in browser**:
   ```
   http://localhost:8000
   ```

## AI Summary Feature

The application uses **Groq API** with **Llama 4 Scout** model for AI summarization:

- **Model**: `meta-llama/llama-4-scout-17b-16e-instruct`
- **API**: Groq Cloud API
- **How it works**: When you click "AI Summarise", the note description is sent to Groq API, which generates a concise summary that is then saved to the database

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `api/create_note.php` | Create a new note |
| GET | `api/get_notes.php` | Get all notes |
| GET | `api/get_note.php?id=X` | Get a specific note |
| PUT | `api/update_note.php?id=X` | Update a note |
| DELETE | `api/delete_note.php?id=X` | Delete a note |
| GET | `api/mock_ai_summary.php?id=X` | Generate AI summary for a note |

## Project Structure

```
notes-app/
├── index.html          # Main HTML file
├── styles.css          # Styles
├── app.js              # JavaScript logic
├── database.sql        # Database schema
├── README.md           # This file
├── .env.example        # Environment variables template
├── .gitignore          # Git ignore rules
└── api/
    ├── config.php      # Database configuration
    ├── create_note.php # Create note endpoint
    ├── get_notes.php   # Get all notes endpoint
    ├── get_note.php    # Get single note endpoint
    ├── update_note.php # Update note endpoint
    ├── delete_note.php # Delete note endpoint
    └── mock_ai_summary.php # AI summary endpoint (Groq API)
```

## How It Works

1. **Create Note**: Fill in title and description, click "Add Note"
2. **View Notes**: All notes are displayed with edit/delete buttons
3. **AI Summary**: Click "AI Summarise" to generate a summary using Groq API
4. **Edit Note**: Click "Edit" to modify an existing note
5. **Delete Note**: Click "Delete" to remove a note

## Validation

- Title and Description are required fields
- Empty fields show error messages
- Form cannot be submitted with invalid data
- Update without changes shows a toast notification

## Toast Notifications

- **Success** (green): Note created, updated, deleted, or summary generated
- **Error** (red): Any operation failure
- **Info** (blue): No changes detected

## Security Note

The `.env` file is gitignored and should never be committed to version control. The `.env.example` file is provided as a template.
