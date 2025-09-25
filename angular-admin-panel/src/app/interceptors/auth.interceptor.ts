import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Get token from localStorage
  const token = localStorage.getItem('token');

  // Check if this is a file upload request (FormData)
  const isFileUpload = req.body instanceof FormData;

  // Clone the request and add headers
  const headers: { [key: string]: string } = {
    'Accept': 'application/json',
    ...(token && { 'Authorization': `Bearer ${token}` })
  };

  // Only set Content-Type for non-file uploads
  if (!isFileUpload) {
    headers['Content-Type'] = 'application/json';
  }

  const authReq = req.clone({
    setHeaders: headers
  });

  console.log('HTTP Request:', {
    url: authReq.url,
    method: authReq.method,
    isFileUpload,
    headers: authReq.headers.keys().reduce((acc, key) => {
      acc[key] = authReq.headers.get(key);
      return acc;
    }, {} as any)
  });

  return next(authReq);
};
