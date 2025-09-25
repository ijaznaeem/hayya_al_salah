import { Routes } from '@angular/router';
import { authGuard } from './guards/auth-guard';

export const routes: Routes = [
  {
    path: 'login',
    loadComponent: () => import('./components/login/login').then(m => m.Login)
  },
  {
    path: '',
    loadComponent: () => import('./components/layout/layout').then(m => m.Layout),
    canActivate: [authGuard],
    children: [
      {
        path: '',
        redirectTo: '/dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        loadComponent: () => import('./components/dashboard/dashboard').then(m => m.Dashboard)
      },
      {
        path: 'users',
        loadComponent: () => import('./components/user-management/user-management').then(m => m.UserManagement)
      },
      {
        path: 'topics',
        loadComponent: () => import('./components/topics-management/topics-management').then(m => m.TopicsManagement)
      },
      {
        path: 'lectures',
        loadComponent: () => import('./components/lectures-management/lectures-management').then(m => m.LecturesManagement)
      },
      {
        path: 'video-processing',
        loadComponent: () => import('./components/video-processing/video-processing').then(m => m.VideoProcessing)
      },
      {
        path: 'analytics',
        loadComponent: () => import('./components/analytics/analytics').then(m => m.Analytics)
      }
    ]
  },
  {
    path: '**',
    redirectTo: '/dashboard'
  }
];
