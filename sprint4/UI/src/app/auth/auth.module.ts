import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {LoginComponent} from './login/login.component';
import {RegisterComponent} from './register/register.component';
import {ForgotPasswordComponent} from './forgot-password/forgot-password.component';
import {RouterModule, Routes} from "@angular/router";
import {ReactiveFormsModule} from "@angular/forms";

const routes: Routes = [
  {path: 'login', component: LoginComponent},
  {path: 'register', component: RegisterComponent},
  {path: 'forgot-password', component: ForgotPasswordComponent},
];

@NgModule({
  imports: [
    ReactiveFormsModule,
    CommonModule,
    RouterModule.forChild(routes),
    LoginComponent,
    RegisterComponent,
    ForgotPasswordComponent
  ]
})
export class AuthModule {
}
