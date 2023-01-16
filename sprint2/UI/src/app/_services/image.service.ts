import {Injectable} from '@angular/core';
import {environment} from "../../environments/environment";
import {map, Observable} from "rxjs";
import {HttpClient} from "@angular/common/http";
import {Image} from "../models/image";

@Injectable({
  providedIn: 'root'
})
export class ImageService {

  private apiURL = environment.apiUrl;

  constructor(private httpClient: HttpClient) {
  }

  getImages(): Observable<Image[]> {
    return this.httpClient.get<Image[]>(this.apiURL + `/images`)
      .pipe(map(this.extractData));
  }

  private extractData = (res: any) => res;

}
