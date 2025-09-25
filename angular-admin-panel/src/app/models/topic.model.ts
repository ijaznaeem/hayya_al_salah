export interface Topic {
  id: number;
  title: string;
  description?: string;
  image_url?: string;
  lessons_count?: number;
  duration?: string;
  created_at: string;
  updated_at?: string;
}

export interface TopicsResponse {
  success: boolean;
  data: Topic[];
  count: number;
}

export interface VideoProcessingStatus {
  status: 'idle' | 'running' | 'completed' | 'error';
  processed: number;
  total: number;
  errors: Array<{
    file: string;
    error: string;
  }>;
  log: string[];
  // New fields for server without FFmpeg
  total_videos?: number;
  needs_processing?: number;
  with_duration?: number;
  ffmpeg_available?: boolean;
  alternative_methods?: boolean;
  note?: string;
}
