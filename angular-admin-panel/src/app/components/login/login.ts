import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import { Auth } from '../../services/auth';

@Component({
  selector: 'app-login',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule
  ],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login {
  loginForm: FormGroup;
  isLoading = false;
  hidePassword = true;

  constructor(
    private fb: FormBuilder,
    private auth: Auth,
    private router: Router,
    private snackBar: MatSnackBar
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required,Validators.email]],
      password: ['', [Validators.required, Validators.minLength(3)]]
    });
  }

  onSubmit() {
    if (this.loginForm.valid && !this.isLoading) {
      this.isLoading = true;

      this.auth.login(this.loginForm.value).subscribe({
        next: (response) => {
          console.log('Login response:', response);
          console.log('Auth service authenticated:', this.auth.isAuthenticated());
          this.isLoading = false;
          this.snackBar.open('Login successful!', 'Close', { duration: 3000 });

          // Use timeout to ensure state is updated before navigation
          setTimeout(() => {
            this.router.navigateByUrl('/dashboard');
          }, 100);
        },
        error: (error) => {
          this.isLoading = false;
          const errorMessage = typeof error === 'string' ? error : 'Login failed. Please check your credentials.';
          this.snackBar.open(errorMessage, 'Close', { duration: 5000 });
        }
      });
    }
  }
}
