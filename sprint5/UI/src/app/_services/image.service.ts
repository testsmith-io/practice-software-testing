import { Injectable } from '@angular/core';
import { environment } from "../../environments/environment";
import { Observable } from "rxjs";
import { HttpClient } from "@angular/common/http";
import { Image } from "../models/image";

@Injectable({
  providedIn: 'root'
})
export class ImageService {
  private readonly apiURL = `${environment.apiUrl}/images`;

  constructor(private httpClient: HttpClient) {}

  getImages(): Observable<Image[]> {
    return this.httpClient.get<Image[]>(this.apiURL);
  }
}
