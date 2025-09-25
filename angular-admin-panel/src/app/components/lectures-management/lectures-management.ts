import { CommonModule } from '@angular/common';
import { HttpEventType } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTableModule } from '@angular/material/table';
import { MatTooltipModule } from '@angular/material/tooltip';
import { Lecture, PaginatedLectures } from '../../models/lecture.model';
import { Topic } from '../../models/topic.model';
import { Api, UploadResponse } from '../../services/api';

@Component({
  selector: 'app-lectures-management',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatTableModule,
    MatPaginatorModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatProgressBarModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatTooltipModule
  ],
  templateUrl: './lectures-management.html',
  styleUrl: './lectures-management.css'
})
export class LecturesManagement implements OnInit {
  displayedColumns: string[] = ['id', 'title', 'lesson_number', 'topic_name', 'duration', 'files', 'actions'];
  lectures: Lecture[] = [];
  topics: Topic[] = [];
  totalLectures = 0;
  pageSize = 10;
  currentPage = 0;
  pageSizeOptions = [5, 10, 25, 50];
  isLoading = false;
  showForm = false;
  lectureForm: FormGroup;
  editingLecture: Lecture | null = null;

  // Upload properties
  videoUploadProgress = 0;
  imageUploadProgress = 0;
  documentUploadProgress = 0;
  videoUploading = false;
  imageUploading = false;
  documentUploading = false;
  selectedVideoFile: File | null = null;
  selectedImageFile: File | null = null;
  selectedDocumentFile: File | null = null;

  constructor(
    private api: Api,
    private dialog: MatDialog,
    private snackBar: MatSnackBar,
    private fb: FormBuilder
  ) {
    this.lectureForm = this.fb.group({
      title: ['', Validators.required],
      description: ['', Validators.required],
      lesson_number: ['', [Validators.required, Validators.min(1)]],
      topic_id: ['', Validators.required],
      video_url: ['', Validators.required],
      thumbnail_url: [''],
      reference_url: [''],
      duration: [0, [Validators.min(0)]] // Optional - will be auto-calculated by service
    });
  }

  ngOnInit() {
    this.loadLectures();
    this.loadTopics();
  }

  loadTopics() {
    this.api.getTopics().subscribe({
      next: (response) => {
        this.topics = response.data || [];
      },
      error: (error) => {
        console.error('Error loading topics:', error);
      }
    });
  }

  loadLectures() {
    this.isLoading = true;
    this.api.getLectures(this.currentPage + 1, this.pageSize).subscribe({
      next: (response: PaginatedLectures) => {
        this.lectures = response.data;
        this.totalLectures = response.total;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading lectures:', error);
        this.snackBar.open('Error loading lectures', 'Close', { duration: 3000 });
        this.isLoading = false;
      }
    });
  }

  onPageChange(event: PageEvent) {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.loadLectures();
  }

  showAddForm() {
    this.showForm = true;
    this.editingLecture = null;
    this.lectureForm.reset();
    this.lectureForm.patchValue({ duration: 0 }); // Set default duration
  }

  editLecture(lecture: Lecture) {
    this.showForm = true;
    this.editingLecture = lecture;
    this.lectureForm.patchValue({
      title: lecture.title,
      description: lecture.description,
      lesson_number: lecture.lesson_number,
      topic_id: lecture.topic_id,
      video_url: lecture.video_url,
      thumbnail_url: lecture.thumbnail_url || '',
      reference_url: lecture.reference_url || '',
      duration: lecture.duration || 0
    });
  }

  cancelForm() {
    this.showForm = false;
    this.editingLecture = null;
    this.lectureForm.reset();
  }

  saveLecture() {
    if (this.lectureForm.valid) {
      const lectureData = this.lectureForm.value;

      if (this.editingLecture) {
        this.api.updateLecture(this.editingLecture.id, lectureData).subscribe({
          next: () => {
            this.snackBar.open('Lecture updated successfully', 'Close', { duration: 3000 });
            this.loadLectures();
            this.cancelForm();
          },
          error: (error) => {
            console.error('Error updating lecture:', error);
            this.snackBar.open('Error updating lecture', 'Close', { duration: 3000 });
          }
        });
      } else {
        this.api.createLecture(lectureData).subscribe({
          next: () => {
            this.snackBar.open('Lecture created successfully', 'Close', { duration: 3000 });
            this.loadLectures();
            this.cancelForm();
          },
          error: (error) => {
            console.error('Error creating lecture:', error);
            this.snackBar.open('Error creating lecture', 'Close', { duration: 3000 });
          }
        });
      }
    }
  }

  deleteLecture(lecture: Lecture) {
    if (confirm(`Are you sure you want to delete "${lecture.title}"?`)) {
      this.api.deleteLecture(lecture.id).subscribe({
        next: () => {
          this.snackBar.open('Lecture deleted successfully', 'Close', { duration: 3000 });
          this.loadLectures();
        },
        error: (error) => {
          console.error('Error deleting lecture:', error);
          this.snackBar.open('Error deleting lecture', 'Close', { duration: 3000 });
        }
      });
    }
  }

  formatDuration(seconds: number | undefined): string {
    if (!seconds) return 'N/A';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  }

  // Helper method to convert duration input
  convertMinutesToSeconds(minutes: number): number {
    return Math.round(minutes * 60);
  }

  convertSecondsToMinutes(seconds: number): number {
    return Math.round(seconds / 60 * 100) / 100; // Round to 2 decimal places
  }

  // File upload methods
  onVideoFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedVideoFile = input.files[0];
      this.uploadVideo();
    }
  }

  onImageFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedImageFile = input.files[0];
      this.uploadImage();
    }
  }

  onDocumentFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.selectedDocumentFile = input.files[0];
      this.uploadDocument();
    }
  }

  uploadVideo() {
    if (!this.selectedVideoFile) return;

    this.videoUploading = true;
    this.videoUploadProgress = 0;

    this.api.uploadVideo(this.selectedVideoFile).subscribe({
      next: (event) => {
        if (event.type === HttpEventType.UploadProgress && event.total) {
          this.videoUploadProgress = Math.round(100 * event.loaded / event.total);
        } else if (event.type === HttpEventType.Response) {
          const response = event.body as UploadResponse;
          if (response && response.success) {
            this.lectureForm.patchValue({ video_url: response.url });
            this.snackBar.open('Video uploaded successfully!', 'Close', { duration: 3000 });
          }
          this.videoUploading = false;
          this.selectedVideoFile = null;
        }
      },
      error: (error) => {
        console.error('Video upload error:', error);
        this.snackBar.open('Video upload failed', 'Close', { duration: 3000 });
        this.videoUploading = false;
        this.selectedVideoFile = null;
      }
    });
  }

  uploadImage() {
    if (!this.selectedImageFile) return;

    this.imageUploading = true;
    this.imageUploadProgress = 0;

    this.api.uploadImage(this.selectedImageFile).subscribe({
      next: (event) => {
        if (event.type === HttpEventType.UploadProgress && event.total) {
          this.imageUploadProgress = Math.round(100 * event.loaded / event.total);
        } else if (event.type === HttpEventType.Response) {
          const response = event.body as UploadResponse;
          if (response && response.success) {
            this.lectureForm.patchValue({ thumbnail_url: response.url });
            this.snackBar.open('Thumbnail uploaded successfully!', 'Close', { duration: 3000 });
          }
          this.imageUploading = false;
          this.selectedImageFile = null;
        }
      },
      error: (error) => {
        console.error('Image upload error:', error);
        this.snackBar.open('Image upload failed', 'Close', { duration: 3000 });
        this.imageUploading = false;
        this.selectedImageFile = null;
      }
    });
  }

  uploadDocument() {
    if (!this.selectedDocumentFile) return;

    this.documentUploading = true;
    this.documentUploadProgress = 0;

    this.api.uploadDocument(this.selectedDocumentFile).subscribe({
      next: (event) => {
        if (event.type === HttpEventType.UploadProgress && event.total) {
          this.documentUploadProgress = Math.round(100 * event.loaded / event.total);
        } else if (event.type === HttpEventType.Response) {
          const response = event.body as UploadResponse;
          if (response && response.success) {
            this.lectureForm.patchValue({ reference_url: response.url });
            this.snackBar.open('Document uploaded successfully!', 'Close', { duration: 3000 });
          }
          this.documentUploading = false;
          this.selectedDocumentFile = null;
        }
      },
      error: (error) => {
        console.error('Document upload error:', error);
        this.snackBar.open('Document upload failed', 'Close', { duration: 3000 });
        this.documentUploading = false;
        this.selectedDocumentFile = null;
      }
    });
  }

  getVideoFileName(): string {
    const url = this.lectureForm.get('video_url')?.value;
    return url ? url.split('/').pop() || '' : '';
  }

  getImageFileName(): string {
    const url = this.lectureForm.get('thumbnail_url')?.value;
    return url ? url.split('/').pop() || '' : '';
  }

  getDocumentFileName(): string {
    const url = this.lectureForm.get('reference_url')?.value;
    return url ? url.split('/').pop() || '' : '';
  }
}
