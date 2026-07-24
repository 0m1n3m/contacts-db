# Deskflow-OP Usage Guide

Welcome to **Deskflow-OP**, a powerful tool for managing your contacts and tasks. This guide will walk you through all features and help you get the most out of the application.

## Table of Contents

1. [Getting Started](#getting-started)
2. [User Roles & Permissions](#user-roles--permissions)
3. [Core Modules](#core-modules)
   - [Contacts Module](#contacts-module)
   - [Tasks Module](#tasks-module)
   - [Projects Module](#projects-module)
4. [Workflows](#workflows)
5. [Advanced Features](#advanced-features)
6. [Troubleshooting](#troubleshooting)
7. [FAQ](#faq)

---

## Getting Started

### Login

1. Navigate to the application URL
2. Enter your email and password
3. Click **Login**
4. Upon successful login, you'll be redirected to the **Contacts** page

**Note:** If you don't have an account, contact your administrator to receive an invitation.

### First Time Setup

1. **Contacts**: Add your first contact or import existing ones
2. **Tasks**: Create a task and assign it to team members
3. **Projects**: Organize tasks by creating projects

---

## User Roles & Permissions

Deskflow-OP uses a role-based access control system. Your role determines what you can do.

### Admin Role

**Full system access.** Admins can:

- ✅ Create, view, edit, and delete **all contacts**
- ✅ Create, view, edit, and delete **all tasks**
- ✅ Assign tasks to any team member
- ✅ Manage task dependencies and tags
- ✅ Create and manage projects
- ✅ Import and export contacts
- ✅ Invite new users to the system
- ✅ View all notifications
- ✅ Access dashboard and reports

### Editor Role

**Limited to own tasks.** Editors can:

- ✅ Create, view, edit contacts
- ✅ Create their own tasks
- ✅ Assign their own tasks to others
- ✅ Manage task comments and attachments
- ✅ Create and manage projects
- ✅ Import and export contacts
- ✅ Accept or reject task assignments
- ❌ **Cannot** delete contacts or tasks created by others
- ❌ **Cannot** invite users

### Viewer Role

**Read-only with limited actions.** Viewers can:

- ✅ View all contacts (read-only)
- ✅ View their assigned tasks
- ✅ Add comments to tasks
- ✅ Attach files to tasks
- ✅ Accept or reject task assignments
- ❌ **Cannot** create or edit contacts
- ❌ **Cannot** create or edit tasks
- ❌ **Cannot** delete anything

---

## Core Modules

### Contacts Module

#### Overview

The Contacts module is your central database for managing people and organizations you work with. Store detailed information, manage relationships, and keep everything organized.

#### Features

- **Contact Database**: Store unlimited contacts with detailed information
- **Custom Fields**: Track relevant data for your business
- **Import/Export**: Bulk import from CSV or export for external use
- **Search & Filter**: Find contacts quickly with advanced search
- **Contact Types**: Distinguish between individuals and organizations

#### Creating a Contact

1. Go to **Contacts** → **New Contact**
2. Fill in the required fields:
   - **Name** (required)
   - **Email** (optional)
   - **Phone** (optional)
   - **Company** (optional)
   - **Position** (optional)
   - **Type** (Person/Organization)
   - **Country** (optional)
   - Additional custom fields
3. Click **Save**

#### Editing a Contact

1. Go to **Contacts**
2. Click on a contact to view details
3. Click **Edit** button
4. Modify the information
5. Click **Save**

#### Deleting a Contact

1. Go to **Contacts**
2. Click on a contact
3. Click **Delete** button
4. Confirm the deletion

**Note:** Only Admin role can delete contacts created by others.

#### Importing Contacts

1. Go to **Contacts** → **Import**
2. Download the **CSV template** (optional)
3. Select your CSV file
4. Click **Preview** to verify the data
5. Click **Import** to complete

**Supported CSV Columns:**
- name
- email
- phone
- company
- position
- country
- type

#### Exporting Contacts

1. Go to **Contacts**
2. Apply any filters (optional)
3. Click **Export** button
4. Choose format (CSV)
5. Download file

---

### Tasks Module

#### Overview

The Tasks module helps you organize work, assign responsibilities, and track progress. Use it to manage individual tasks, collaborate with team members, and meet deadlines.

#### Features

- **Task Creation**: Create tasks with detailed descriptions
- **Assignments**: Assign tasks to team members
- **Kanban Board**: Visualize workflow with status columns
- **Priority Levels**: High, Medium, Low priority tasks
- **Due Dates**: Set and track deadlines
- **Task Comments**: Collaborate and discuss tasks
- **File Attachments**: Attach relevant documents
- **Task Dependencies**: Link related tasks
- **Tags**: Categorize and organize tasks
- **Notifications**: Stay updated on task changes

#### Creating a Task

1. Go to **Tasks** → **New Task**
2. Fill in task details:
   - **Title** (required) - Clear, descriptive title
   - **Description** (optional) - Detailed information about the task
   - **Project** (optional) - Assign to a project
   - **Priority** (optional) - High/Medium/Low
   - **Due Date** (optional) - Deadline for completion
   - **Assignee** (optional) - Person responsible
3. Click **Save**

#### Task Status Workflow

Tasks have the following statuses:

- **To Do** - Task not started
- **In Progress** - Currently being worked on
- **In Review** - Waiting for review/approval
- **Done** - Task completed

#### Changing Task Status

**Option 1: Kanban Board**
1. Go to **Tasks** → **Board**
2. Drag tasks between columns to change status

**Option 2: Task Detail**
1. Open a task
2. Click the status dropdown
3. Select new status
4. Task updates automatically

#### Assigning Tasks

1. Open a task
2. Click **Assign** or find the assignee field
3. Select a team member from the list
4. Task assignee receives a notification
5. Assignee can **Accept** or **Reject** the task

#### Task Comments

Collaborate with team members directly on tasks:

1. Open a task
2. Scroll to **Comments** section
3. Type your comment
4. Click **Post Comment**
5. Team members are notified

#### File Attachments

Add supporting documents to tasks:

1. Open a task
2. Scroll to **Attachments** section
3. Click **Add Attachment** or drag files
4. File uploads and appears in the list
5. Click file name to download

**Supported Formats:** PDF, images (JPG, PNG), documents (DOC, DOCX), spreadsheets, etc.

#### Task Tags

Organize tasks with tags:

1. Open a task
2. Click **Add Tag**
3. Select existing tag or create new one
4. Click to save
5. Filter by tags in task list

#### Task Dependencies

Link related tasks:

1. Open a task
2. Scroll to **Dependencies** section
3. Click **Add Dependency**
4. Search and select another task
5. This creates a relationship between tasks

#### Viewing Tasks

**List View:**
1. Go to **Tasks**
2. See all your tasks in a table format
3. Click column headers to sort
4. Use filters for advanced search

**Board View (Kanban):**
1. Go to **Tasks** → **Board**
2. See tasks organized by status
3. Drag tasks between columns
4. Click tasks for details

---

### Projects Module

#### Overview

Projects help you organize related tasks, contacts, and team members. Group work by client, product, or initiative.

#### Features

- **Project Creation**: Create projects for organizing work
- **Task Association**: Link tasks to projects
- **Team Members**: Invite collaborators
- **Project Status**: Track overall progress
- **Project Details**: Store project-specific information

#### Creating a Project

1. Go to **Projects** → **New Project**
2. Fill in details:
   - **Name** (required) - Project title
   - **Description** (optional) - Project details
   - **Status** (optional) - Active/On Hold/Completed
   - **Start Date** (optional)
   - **End Date** (optional)
3. Click **Save**

#### Viewing Project Details

1. Go to **Projects**
2. Click on a project
3. View all associated tasks
4. See project statistics and progress

#### Adding Tasks to Projects

When creating or editing a task, select the project from the **Project** dropdown. Tasks will automatically appear on the project page.

#### Editing Projects

1. Open a project
2. Click **Edit**
3. Modify details
4. Click **Save**

#### Deleting Projects

1. Open a project
2. Click **Delete** button
3. Confirm deletion

**Note:** Only Admin role can delete projects.

---

## Workflows

### Standard Workflow: Login to Task Completion

Follow this typical workflow for a complete task cycle:

#### Step 1: Login

1. Open application URL
2. Enter email and password
3. Click Login
4. Land on Contacts page

#### Step 2: Create or View Contact

1. Click on Contacts
2. Either: a. View existing contact (click on name) b. Create new contact (click "New Contact")
3. Note contact details (Name, Company, etc.)

#### Step 3: Create Task

1. Go to Tasks
2. Click "New Task"
3. Enter task details:
    3a.  Title (required)
    3b. Description
    3c. Project (optional)
    3d. Priority
    3e. Due date
4. Click Save
5. Task appears in "To Do" status

#### Step 4: Assign Task

1. Open the task you created
2. Click Assign
3. Select team member
4. Team member receives notification
5. Click Save

#### Step 5: Task Assignee Accepts/Rejects

Task Assignee:

1. Receives notification
2. Goes to Tasks
3. Finds assigned task
4. Clicks Accept or Reject
5. If accepted, task becomes their responsibility

#### Step 6: Work on Task

1. Update task status: To Do → In Progress
2. Add comments for updates
3. Attach relevant files
4. Update progress

#### Step 7: Request Review (Optional)

1. Change status: In Progress → In Review
2. Notify reviewer via comment
3. Reviewer examines task
4. Provides feedback via comments

#### Step 8: Complete Task

1. Make final updates
2. Change status: In Review → Done
3. Add completion comment
4. Close task or archive


---

## Advanced Features

### Notifications

Stay informed about task updates:

#### Types of Notifications
- **Task Assigned**: You've been assigned a new task
- **Task Commented**: Someone commented on your task
- **Task Status Changed**: Task status was updated
- **File Attached**: File added to a task
- **Mention**: Someone mentioned you

#### Viewing Notifications
1. Click **Bell icon** in header
2. View recent notifications
3. Click to navigate to task
4. Mark as read

#### Managing Notifications
1. Click **Notifications** in menu
2. View all notifications
3. Mark individual as read
4. Or mark all as read

### Dashboard (Admin Only)

Access at **/dashboard** for system overview:

- **User Statistics**: Total users, active users
- **Task Statistics**: Total, in progress, completed
- **Recent Activity**: Latest actions
- **System Health**: Application status

---

## Troubleshooting

### Common Issues & Solutions

#### Issue: "Permission Denied" Error

**Solution:**
- Check your user role (Admin/Editor/Viewer)
- Verify you have permission for this action
- Contact administrator to upgrade role if needed

#### Issue: Contact/Task Not Appearing

**Solution:**
- Refresh page (Ctrl+F5 or Cmd+Shift+R)
- Check filters are not hiding the item
- Verify you have permission to view it
- Try searching for the item

#### Issue: Attachment Upload Failed

**Solution:**
- Check file size (usually max 10MB)
- Verify file format is supported
- Try uploading again
- Clear browser cache if persistent

#### Issue: Task Assignment Notification Not Received

**Solution:**
- Check notification settings
- Verify internet connection
- Refresh notifications page
- Check spam folder if email notification

#### Issue: Import CSV Failed

**Solution:**
- Verify CSV format matches template
- Check all required columns present
- Ensure no duplicate emails
- Download fresh template and retry

#### Issue: Can't Edit Contact/Task

**Solution:**
- Verify your role allows editing
- Check if you're the creator (for Editors)
- Refresh page to ensure latest data
- Contact Admin if issue persists

### Performance Tips

- **Archive old tasks** to improve list performance
- **Use filters** to narrow down large lists
- **Clear notifications** periodically
- **Logout** when not using app for extended periods
- **Clear browser cache** if experiencing slowness

---

## FAQ

### General Questions

**Q: Who can invite new users?**
A: Only Admin users can invite new team members. They can do this at Admin → Invitations.

**Q: Can I change my password?**
A: Yes. Go to Profile → Change Password to update your password.

**Q: What happens if I delete a contact?**
A: Deleted contacts cannot be recovered. Only Admin users can delete contacts. If unsure, contact your administrator.

**Q: Can I export my data?**
A: Yes. Go to Contacts and click Export to download as CSV.

### Tasks & Workflows

**Q: Can I assign a task to multiple people?**
A: Currently, tasks are assigned to one person at a time. You can mention others in comments for collaboration.

**Q: What's the difference between In Review and Done?**
A: "In Review" means waiting for approval. "Done" means task is complete and approved.

**Q: Can I duplicate a task?**
A: Not directly, but you can create a new task with the same details and copy information.

**Q: How do I merge or combine tasks?**
A: Create one task with complete details and close the other. You can link them with dependencies.

**Q: Can tasks be scheduled for future dates?**
A: Yes. Set the due date when creating or editing a task.

### Contacts

**Q: Can I merge duplicate contacts?**
A: Currently no automatic merge. Best practice: delete duplicate and keep the complete one.

**Q: What information is required for a contact?**
A: Only "Name" is required. Other fields are optional based on your needs.

**Q: Can I tag contacts?**
A: Not currently. You can organize by Company or Type fields.

**Q: How many contacts can I store?**
A: Unlimited contacts can be stored in the system.

### Projects

**Q: Do I need to create a project for tasks?**
A: No. Projects are optional for organizing related tasks. You can create tasks without projects.

**Q: Can I move tasks between projects?**
A: Yes. Edit the task and change the Project field.

**Q: Can projects have subtasks?**
A: Not directly. Use task dependencies to create relationships.

### Access & Permissions

**Q: I'm an Editor. Why can't I delete this contact?**
A: Editors can only delete contacts they created. Contact an Admin to delete contacts by others.

**Q: Can I change someone's role?**
A: Only Admins can change user roles. Contact your Admin.

**Q: Why can't I see this task?**
A: Your role might not have access, or you don't have permission. Confirm with task creator or Admin.

### Technical

**Q: What browsers are supported?**
A: Chrome, Firefox, Safari, and Edge. Use latest version for best experience.

**Q: Can I use this on mobile?**
A: Yes. The application is responsive and works on tablets and phones.

**Q: Is there an API?**
A: API access is available for integrations. Contact your administrator.

**Q: Where is my data stored?**
A: Data is stored securely on our servers. Contact administrator for specific details.

---

## Getting Help

### Resources

- **In-App Help**: Visit `/help` for interactive documentation
- **Contact Admin**: Reach out to your system administrator
- **Report Issues**: Use feedback form in the application

### Best Practices

1. **Use clear task titles** - Makes searching easier
2. **Add descriptions** - Provides context for team members
3. **Set due dates** - Helps prioritize work
4. **Use projects** - Organizes related tasks
5. **Comment regularly** - Keeps team updated
6. **Use tags** - Categorizes and filters tasks
7. **Attach files** - Provides supporting documentation

---

## Version Information

- **Application**: Deskflow-OP v1.0
- **Last Updated**: July 2026
- **For Support**: Contact your administrator

---

**Thank you for using Deskflow-OP!** We're continuously improving the application. Please share feedback with your administrator.