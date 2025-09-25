import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTableModule } from '@angular/material/table';
import { Topic } from '../../models/topic.model';
import { Api } from '../../services/api';

@Component({
  selector: 'app-topics-management',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatDialogModule
  ],
  templateUrl: './topics-management.html',
  styleUrl: './topics-management.css'
})
export class TopicsManagement implements OnInit {
  displayedColumns: string[] = ['id', 'title', 'description', 'lessons_count', 'created_at', 'actions'];
  topics: Topic[] = [];
  isLoading = false;
  showForm = false;
  topicForm: FormGroup;
  editingTopic: Topic | null = null;

  constructor(
    private api: Api,
    private snackBar: MatSnackBar,
    private fb: FormBuilder
  ) {
    this.topicForm = this.fb.group({
      title: ['', Validators.required],
      description: ['', Validators.required],
      image_url: ['']
    });
  }

  ngOnInit() {
    this.loadTopics();
  }

  loadTopics() {
    this.isLoading = true;
    this.api.getTopics().subscribe({
      next: (response) => {
        this.topics = response.data || [];
        this.isLoading = false;
      },
      error: (error) => {
        this.isLoading = false;
        this.snackBar.open('Error loading topics', 'Close', { duration: 3000 });
      }
    });
  }

  showAddForm() {
    this.showForm = true;
    this.editingTopic = null;
    this.topicForm.reset();
  }

  editTopic(topic: Topic) {
    this.showForm = true;
    this.editingTopic = topic;
    this.topicForm.patchValue({
      title: topic.title,
      description: topic.description,
      image_url: topic.image_url
    });
  }

  cancelForm() {
    this.showForm = false;
    this.editingTopic = null;
    this.topicForm.reset();
  }

  saveTopic() {
    if (this.topicForm.valid) {
      const topicData = this.topicForm.value;

      if (this.editingTopic) {
        this.api.updateTopic(this.editingTopic.id, topicData).subscribe({
          next: () => {
            this.snackBar.open('Topic updated successfully', 'Close', { duration: 3000 });
            this.loadTopics();
            this.cancelForm();
          },
          error: () => {
            this.snackBar.open('Error updating topic', 'Close', { duration: 3000 });
          }
        });
      } else {
        this.api.createTopic(topicData).subscribe({
          next: () => {
            this.snackBar.open('Topic created successfully', 'Close', { duration: 3000 });
            this.loadTopics();
            this.cancelForm();
          },
          error: () => {
            this.snackBar.open('Error creating topic', 'Close', { duration: 3000 });
          }
        });
      }
    }
  }

  deleteTopic(topic: Topic) {
    if (confirm(`Are you sure you want to delete topic "${topic.title}"?`)) {
      this.api.deleteTopic(topic.id).subscribe({
        next: () => {
          this.snackBar.open('Topic deleted successfully', 'Close', { duration: 3000 });
          this.loadTopics();
        },
        error: () => {
          this.snackBar.open('Error deleting topic', 'Close', { duration: 3000 });
        }
      });
    }
  }
}
