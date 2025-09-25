import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { BehaviorSubject, Observable } from 'rxjs';
import { LoginRequest, User } from '../models/user.model';
import { Api } from './api';

@Injectable({
  providedIn: 'root'
})
export class Auth {
  private currentUserSubject = new BehaviorSubject<User | null>(null);
  public currentUser$ = this.currentUserSubject.asObservable();
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(this.hasToken());
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(
    private api: Api,
    private router: Router
  ) {
    // Initialize user from localStorage if token exists
    if (this.hasToken()) {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.currentUserSubject.next(JSON.parse(userData));
      }
    }
  }

  login(credentials: LoginRequest): Observable<any> {
    return new Observable(observer => {
      this.api.login(credentials).subscribe({
        next: (response) => {
          // Check if response has user and token (successful login)
          if (response.user && response.token) {
            // Store user data
            localStorage.setItem('user', JSON.stringify(response.user));
            localStorage.setItem('token', response.token);

            this.currentUserSubject.next(response.user);
            this.isAuthenticatedSubject.next(true);
            observer.next(response);
          } else {
            observer.error(response.message || 'Login failed');
          }
          observer.complete();
        },
        error: (error) => {
          observer.error(error);
        }
      });
    });
  }

  logout(): void {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    this.currentUserSubject.next(null);
    this.isAuthenticatedSubject.next(false);
    this.router.navigate(['/login']);
  }

  isAuthenticated(): boolean {
    return this.hasToken();
  }

  getCurrentUser(): User | null {
    return this.currentUserSubject.value;
  }

  private hasToken(): boolean {
    return !!localStorage.getItem('token') && !!localStorage.getItem('user');
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }
}
