export interface Lecture {
  id: number;
  title: string;
  description: string;
  lesson_number: number;
  topic_id: number;
  video_url: string;
  thumbnail_url?: string;
  reference_url?: string;
  duration?: number;
  created_at: string;
  topic_name?: string;
}

export interface PaginatedLectures {
  data: Lecture[];
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
}
