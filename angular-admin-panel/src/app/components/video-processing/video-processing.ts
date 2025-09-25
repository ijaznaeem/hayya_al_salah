import { CommonModule } from '@angular/common';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { VideoProcessingStatus } from '../../models/topic.model';
import { Api } from '../../services/api';

@Component({
  selector: 'app-video-processing',
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressBarModule,
    MatSnackBarModule,
    MatCheckboxModule
  ],
  templateUrl: './video-processing.html',
  styleUrl: './video-processing.css'
})
export class VideoProcessing implements OnInit, OnDestroy {
  status: VideoProcessingStatus = {
    status: 'idle',
    processed: 0,
    total: 0,
    errors: [],
    log: [],
    total_videos: 0,
    needs_processing: 0,
    with_duration: 0,
    ffmpeg_available: false,
    alternative_methods: false
  };

  dryRun = false;
  force = false;
  private statusCheckInterval: any;

  constructor(
    private api: Api,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit() {
    this.checkStatus();
  }

  ngOnDestroy() {
    if (this.statusCheckInterval) {
      clearInterval(this.statusCheckInterval);
    }
  }

  checkStatus() {
    this.api.getVideoProcessingStatus().subscribe({
      next: (status) => {
        this.status = status;
      },
      error: (error) => {
        console.error('Error checking status:', error);
      }
    });
  }

  startProcessing() {
    this.api.startVideoProcessing(this.dryRun, this.force).subscribe({
      next: (response) => {
        this.snackBar.open('Video processing started', 'Close', { duration: 3000 });
        this.startStatusPolling();
      },
      error: (error) => {
        console.error('Error starting processing:', error);
        this.snackBar.open('Error starting video processing', 'Close', { duration: 3000 });
      }
    });
  }

  private startStatusPolling() {
    this.statusCheckInterval = setInterval(() => {
      this.checkStatus();
      if (this.status.status === 'completed' || this.status.status === 'error') {
        clearInterval(this.statusCheckInterval);
      }
    }, 2000);
  }

  get progressPercentage(): number {
    if (this.status.total === 0) return 0;
    return Math.round((this.status.processed / this.status.total) * 100);
  }

  get isRunning(): boolean {
    return this.status.status === 'running';
  }
}
