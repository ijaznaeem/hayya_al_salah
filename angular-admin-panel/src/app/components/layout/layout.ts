import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatToolbarModule } from '@angular/material/toolbar';
import { Router, RouterOutlet } from '@angular/router';
import { Auth } from '../../services/auth';

@Component({
  selector: 'app-layout',
  imports: [
    CommonModule,
    RouterOutlet,
    MatToolbarModule,
    MatSidenavModule,
    MatListModule,
    MatIconModule,
    MatButtonModule
  ],
  templateUrl: './layout.html',
  styleUrl: './layout.css'
})
export class Layout {
  constructor(
    public authService: Auth,
    private router: Router
  ) {}

  menuItems = [
    { path: '/dashboard', icon: 'dashboard', label: 'Dashboard' },
    { path: '/users', icon: 'people', label: 'Users' },
    { path: '/topics', icon: 'category', label: 'Topics' },
    { path: '/lectures', icon: 'video_library', label: 'Lectures' },
    { path: '/video-processing', icon: 'smart_display', label: 'Video Processing' },
    { path: '/analytics', icon: 'analytics', label: 'Analytics' }
  ];

  navigate(path: string) {
    this.router.navigate([path]);
  }

  logout() {
    this.authService.logout();
  }
}
