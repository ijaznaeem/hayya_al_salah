import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDialog } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTableModule } from '@angular/material/table';
import { User } from '../../models/user.model';
import { Api } from '../../services/api';

@Component({
  selector: 'app-user-management',
  imports: [
    CommonModule,
    MatTableModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatPaginatorModule,
    MatProgressSpinnerModule,
    MatSnackBarModule
  ],
  templateUrl: './user-management.html',
  styleUrl: './user-management.css'
})
export class UserManagement implements OnInit {
  displayedColumns: string[] = ['id', 'username', 'email', 'role', 'created_at', 'actions'];
  users: User[] = [];
  totalUsers = 0;
  pageSize = 10;
  pageIndex = 0;
  isLoading = false;

  constructor(
    private api: Api,
    private snackBar: MatSnackBar,
    private dialog: MatDialog
  ) {}

  ngOnInit() {
    this.loadUsers();
  }

  loadUsers() {
    this.isLoading = true;
    this.api.getUsers(this.pageIndex + 1, this.pageSize).subscribe({
      next: (response) => {
        this.users = response.data || [];
        this.totalUsers = response.total || 0;
        this.isLoading = false;
      },
      error: (error) => {
        this.isLoading = false;
        this.snackBar.open('Error loading users', 'Close', { duration: 3000 });
      }
    });
  }

  onPageChange(event: PageEvent) {
    this.pageSize = event.pageSize;
    this.pageIndex = event.pageIndex;
    this.loadUsers();
  }

  deleteUser(user: User) {
    if (confirm(`Are you sure you want to delete user "${user.username}"?`)) {
      this.api.deleteUser(user.id).subscribe({
        next: () => {
          this.snackBar.open('User deleted successfully', 'Close', { duration: 3000 });
          this.loadUsers();
        },
        error: () => {
          this.snackBar.open('Error deleting user', 'Close', { duration: 3000 });
        }
      });
    }
  }
}
