<?php

namespace HayyaAlSalah\Controllers;

use HayyaAlSalah\Models\Notification;

class NotificationController extends BaseController
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    /**
     * Get all notifications with pagination
     */
    public function index(): void
    {
        try {
            $limit = (int) ($_GET['limit'] ?? 50);
            $offset = (int) ($_GET['offset'] ?? 0);
            $userId = $_GET['user_id'] ?? null;
            
            // Ensure reasonable limits
            $limit = min($limit, 100);
            $limit = max($limit, 1);
            $offset = max($offset, 0);

            $notifications = $this->notificationModel->getAll($limit, $offset, $userId);
            $total = $this->notificationModel->count();

            $this->sendResponse([
                'success' => true,
                'data' => $notifications,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ]
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to fetch notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific notification by ID
     */
    public function show(string $id): void
    {
        try {
            $notification = $this->notificationModel->getById($id);
            
            if (!$notification) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Notification not found'
                ], 404);
                return;
            }

            $this->sendResponse([
                'success' => true,
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to fetch notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new notification (admin only)
     */
    public function store(): void
    {
        try {
            // Check if user is admin (you may need to implement admin authentication)
            // if (!$this->isAdmin()) {
            //     $this->sendResponse(['error' => 'Unauthorized'], 401);
            //     return;
            // }

            $data = $this->getRequestData();
            
            // Validate required fields
            if (empty($data['title']) || empty($data['message'])) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Title and message are required'
                ], 400);
                return;
            }

            // Create notification
            $id = $this->notificationModel->create([
                'title' => $data['title'],
                'message' => $data['message'],
                'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s')
            ]);

            // Get the created notification
            $notification = $this->notificationModel->getById($id);

            $this->sendResponse([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification
            ], 201);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to create notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a notification (admin only)
     */
    public function update(string $id): void
    {
        try {
            // Check if user is admin (you may need to implement admin authentication)
            // if (!$this->isAdmin()) {
            //     $this->sendResponse(['error' => 'Unauthorized'], 401);
            //     return;
            // }

            $data = $this->getRequestData();
            
            if (empty($data)) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'No data provided for update'
                ], 400);
                return;
            }

            $updated = $this->notificationModel->update($id, $data);
            
            if (!$updated) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Notification not found or no changes made'
                ], 404);
                return;
            }

            // Get the updated notification
            $notification = $this->notificationModel->getById($id);

            $this->sendResponse([
                'success' => true,
                'message' => 'Notification updated successfully',
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to update notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification (admin only)
     */
    public function delete(string $id): void
    {
        try {
            // Check if user is admin (you may need to implement admin authentication)
            // if (!$this->isAdmin()) {
            //     $this->sendResponse(['error' => 'Unauthorized'], 401);
            //     return;
            // }

            $deleted = $this->notificationModel->delete($id);
            
            if (!$deleted) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Notification not found'
                ], 404);
                return;
            }

            $this->sendResponse([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to delete notification',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent notifications
     */
    public function recent(): void
    {
        try {
            $days = (int) ($_GET['days'] ?? 30);
            $userId = $_GET['user_id'] ?? null;
            $days = min($days, 365); // Max 1 year
            $days = max($days, 1);   // Min 1 day

            $notifications = $this->notificationModel->getRecent($days, $userId);

            $this->sendResponse([
                'success' => true,
                'data' => $notifications,
                'filter' => [
                    'days' => $days,
                    'count' => count($notifications)
                ]
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to fetch recent notifications',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function stats(): void
    {
        try {
            $userId = $_GET['user_id'] ?? null;
            $total = $this->notificationModel->count();
            $unread = $this->notificationModel->getUnreadCount($userId);
            $recent30Days = count($this->notificationModel->getRecent(30, $userId));
            $recent7Days = count($this->notificationModel->getRecent(7, $userId));
            $recent24Hours = count($this->notificationModel->getRecent(1, $userId));

            $this->sendResponse([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'unread' => $unread,
                    'recent_30_days' => $recent30Days,
                    'recent_7_days' => $recent7Days,
                    'recent_24_hours' => $recent24Hours
                ]
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to fetch notification statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(): void
    {
        try {
            $userId = $_GET['user_id'] ?? null;
            $count = $this->notificationModel->getUnreadCount($userId);

            $this->sendResponse([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to fetch unread count',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id): void
    {
        try {
            $success = $this->notificationModel->markAsRead($id);
            
            if (!$success) {
                $this->sendResponse([
                    'success' => false,
                    'error' => 'Notification not found or already read'
                ], 404);
                return;
            }

            $this->sendResponse([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to mark notification as read',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): void
    {
        try {
            $userId = $_GET['user_id'] ?? null;
            $success = $this->notificationModel->markAllAsRead($userId);

            $this->sendResponse([
                'success' => true,
                'message' => $success ? 'All notifications marked as read' : 'No unread notifications found'
            ]);
        } catch (\Exception $e) {
            $this->sendResponse([
                'success' => false,
                'error' => 'Failed to mark all notifications as read',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
