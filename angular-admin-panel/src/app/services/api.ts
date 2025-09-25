import { HttpClient, HttpEvent, HttpEventType, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { Lecture, PaginatedLectures } from '../models/lecture.model';
import { Topic, TopicsResponse, VideoProcessingStatus } from '../models/topic.model';
import { LoginRequest, LoginResponse } from '../models/user.model';

// Upload response interface
export interface UploadResponse {
  success: boolean;
  url: string;
  filename: string;
  original_name: string;
  size: number;
  type: string;
}

@Injectable({
  providedIn: 'root'
})
export class Api {
  private baseUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  // Auth endpoints
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.baseUrl}/api/auth/login`, credentials);
  }

  // User management
  getUsers(page = 1, perPage = 10): Observable<any> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('per_page', perPage.toString());
    return this.http.get<any>(`${this.baseUrl}/api/admin/users`, { params });
  }

  deleteUser(id: number): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/admin/users/${id}`);
  }

  // Lecture management
  getLectures(page = 1, perPage = 10): Observable<PaginatedLectures> {
    const params = new HttpParams()
      .set('page', page.toString())
      .set('per_page', perPage.toString());
    return this.http.get<PaginatedLectures>(`${this.baseUrl}/api/lectures`, { params });
  }

  createLecture(lecture: Partial<Lecture>): Observable<Lecture> {
    return this.http.post<Lecture>(`${this.baseUrl}/api/lectures`, lecture);
  }

  updateLecture(id: number, lecture: Partial<Lecture>): Observable<Lecture> {
    return this.http.put<Lecture>(`${this.baseUrl}/api/lectures/${id}`, lecture);
  }

  deleteLecture(id: number): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/lectures/${id}`);
  }

  // Topic management
  getTopics(): Observable<TopicsResponse> {
    return this.http.get<TopicsResponse>(`${this.baseUrl}/api/topics`);
  }

  createTopic(topic: Partial<Topic>): Observable<Topic> {
    return this.http.post<Topic>(`${this.baseUrl}/api/topics`, topic);
  }

  updateTopic(id: number, topic: Partial<Topic>): Observable<Topic> {
    return this.http.put<Topic>(`${this.baseUrl}/api/topics/${id}`, topic);
  }

  deleteTopic(id: number): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/topics/${id}`);
  }

  // Video processing
  getVideoProcessingStatus(): Observable<VideoProcessingStatus> {
    return this.http.get<VideoProcessingStatus>(`${this.baseUrl}/api/admin/videos/duration/status`);
  }

  startVideoProcessing(dryRun = false, force = false): Observable<any> {
    const params = new HttpParams()
      .set('dry_run', dryRun.toString())
      .set('force', force.toString());
    return this.http.post(`${this.baseUrl}/api/admin/videos/duration/process`, {}, { params });
  }

  setVideoDuration(lectureId: number, duration: string): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/admin/videos/duration/set`, {
      lecture_id: lectureId,
      duration: duration
    });
  }

  // Analytics
  getAnalytics(): Observable<any> {
    return this.http.get(`${this.baseUrl}/api/admin/dashboard`);
  }

  // File Upload methods
  uploadVideo(file: File): Observable<HttpEvent<UploadResponse>> {
    const formData = new FormData();
    formData.append('file', file);

    return this.http.post<UploadResponse>(`${this.baseUrl}/api/upload/video`, formData, {
      reportProgress: true,
      observe: 'events'
    }).pipe(
      map(event => {
        if (event.type === HttpEventType.Response && event.body) {
          // Convert relative URL to absolute URL
          const response = { ...event.body };
          if (response.url && !response.url.startsWith('http')) {
            response.url = `${this.baseUrl}${response.url}`;
          }
          return event.clone({ body: response });
        }
        return event;
      })
    );
  }

  uploadImage(file: File): Observable<HttpEvent<UploadResponse>> {
    const formData = new FormData();
    formData.append('file', file);

    return this.http.post<UploadResponse>(`${this.baseUrl}/api/upload/image`, formData, {
      reportProgress: true,
      observe: 'events'
    }).pipe(
      map(event => {
        if (event.type === HttpEventType.Response && event.body) {
          // Convert relative URL to absolute URL
          const response = { ...event.body };
          if (response.url && !response.url.startsWith('http')) {
            response.url = `${this.baseUrl}${response.url}`;
          }
          return event.clone({ body: response });
        }
        return event;
      })
    );
  }

  uploadDocument(file: File): Observable<HttpEvent<UploadResponse>> {
    const formData = new FormData();
    formData.append('file', file);

    return this.http.post<UploadResponse>(`${this.baseUrl}/api/upload/document`, formData, {
      reportProgress: true,
      observe: 'events'
    }).pipe(
      map(event => {
        if (event.type === HttpEventType.Response && event.body) {
          // Convert relative URL to absolute URL
          const response = { ...event.body };
          if (response.url && !response.url.startsWith('http')) {
            response.url = `${this.baseUrl}${response.url}`;
          }
          return event.clone({ body: response });
        }
        return event;
      })
    );
  }

  deleteFile(url: string): Observable<any> {
    return this.http.delete(`${this.baseUrl}/api/upload/file`, {
      body: { url }
    });
  }
}
