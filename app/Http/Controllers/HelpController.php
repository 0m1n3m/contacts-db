<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HelpController extends Controller
{
    /**
     * Show the help documentation page
     */
    public function index()
    {
        $helpContent = $this->getHelpContent();
        
        return Inertia::render('Help/index', [
            'helpContent' => $helpContent,
        ]);
    }

    /**
     * Get structured help content
     */
    private function getHelpContent(): array
    {
        return [
            'sections' => [
                [
                    'id' => 'getting-started',
                    'title' => 'Getting Started',
                    'icon' => 'rocket',
                ],
                [
                    'id' => 'roles-permissions',
                    'title' => 'Roles & Permissions',
                    'icon' => 'shield',
                ],
                [
                    'id' => 'contacts',
                    'title' => 'Contacts Module',
                    'icon' => 'users',
                ],
                [
                    'id' => 'tasks',
                    'title' => 'Tasks Module',
                    'icon' => 'checklist',
                ],
                [
                    'id' => 'projects',
                    'title' => 'Projects Module',
                    'icon' => 'folder',
                ],
                [
                    'id' => 'notifications',
                    'title' => 'Notifications',
                    'icon' => 'bell',
                ],
                [
                    'id' => 'workflows',
                    'title' => 'Common Workflows',
                    'icon' => 'flow',
                ],
                [
                    'id' => 'troubleshooting',
                    'title' => 'Troubleshooting',
                    'icon' => 'wrench',
                ],
                [
                    'id' => 'faq',
                    'title' => 'FAQ',
                    'icon' => 'question',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Who can invite new users?',
                    'answer' => 'Only Admin users can invite new team members through Admin > Invitations.'
                ],
                [
                    'question' => 'Can I change my password?',
                    'answer' => 'Yes, go to Profile > Change Password.'
                ],
                [
                    'question' => 'What happens if I delete a contact?',
                    'answer' => 'Deleted contacts cannot be recovered. Only Admins can delete contacts.'
                ],
                [
                    'question' => 'Can I export my data?',
                    'answer' => 'Yes, go to Contacts and click Export to download as CSV.'
                ],
                [
                    'question' => 'Can tasks be assigned to multiple people?',
                    'answer' => 'Currently, tasks are assigned to one person. Mention others in comments for collaboration.'
                ],
                [
                    'question' => 'Do I need to create a project for tasks?',
                    'answer' => 'No, projects are optional. You can create tasks independently.'
                ],
                [
                    'question' => 'Why can\'t I delete this contact?',
                    'answer' => 'Editors can only delete contacts they created. Contact an Admin for others.'
                ],
                [
                    'question' => 'Is there mobile support?',
                    'answer' => 'Yes, the application is responsive and works on mobile devices.'
                ]
            ],
            'userRole' => auth()->user()->role ?? 'viewer',
        ];
    }
}