import {Component, inject, OnInit} from '@angular/core';
import {FormBuilder, FormGroup, ReactiveFormsModule, Validators} from "@angular/forms";
import {ActivatedRoute, Router} from "@angular/router";
import {first} from "rxjs/operators";
import {Product} from "../../models/product";
import {ProductService} from "../../_services/product.service";
import {Brand} from "../../models/brand";
import {Category} from "../../models/category";
import {BrandService} from "../../_services/brand.service";
import {CategoryService} from "../../_services/category.service";
import {Image} from "../../models/image";
import {ImageService} from "../../_services/image.service";
import {NgClass} from "@angular/common";

@Component({
  selector: 'app-products-add-edit',
  templateUrl: './products-add-edit.component.html',
  imports: [
    ReactiveFormsModule,
    NgClass
  ],
  styleUrls: ['./products-add-edit.component.css']
})
export class ProductsAddEditComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly productService = inject(ProductService);
  private readonly brandService = inject(BrandService);
  private readonly categoryService = inject(CategoryService);
  private readonly imageService = inject(ImageService);

  form: FormGroup;
  products!: Product[];
  brands!: Brand[];
  categories!: Category[];
  images!: Image[];
  id: string;
  selectedImageId: number;
  selectedImage: any;
  isAddMode: boolean;
  submitted: boolean = false;
  isUpdated: boolean = false;
  hideAlert: boolean = false;
  error: string;

  ngOnInit(): void {
    this.id = this.route.snapshot.params['id'];
    this.isAddMode = !this.id;

    this.form = this.formBuilder.group({
      id: ['', []],
      name: ['', [Validators.required]],
      description: ['', [Validators.required]],
      stock: ['', [Validators.required]],
      price: ['', [Validators.required]],
      brand_id: ['', [Validators.required]],
      category_id: ['', [Validators.required]],
      product_image_id: ['', [Validators.required]],
      is_location_offer: ['', []],
      is_rental: ['', []]
    });

    this.brandService.getBrands()
      .pipe(first())
      .subscribe((brands) => this.brands = brands);

    this.categoryService.getCategories()
      .pipe(first())
      .subscribe((categories) => this.categories = categories);

    this.imageService.getImages()
      .pipe(first())
      .subscribe((images) => {
        this.images = images
        if (!this.isAddMode) {
          this.productService.getById(this.id)
            .pipe(first())
            .subscribe(x => {
              this.form.patchValue(x)
              this.selectedImage = this.images.find((el: Image) => {
                return el?.id == x.product_image_id;
              });
            });
        }
      });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;
    this.isUpdated = false;

    if (this.form.invalid) {
      return;
    }

    if (this.isAddMode) {
      this.createProduct();
    } else {
      this.updateProduct();
    }
  }

  private createProduct() {
    if (!this.form.get('is_rental')?.value) {
      this.form.get('is_rental')?.setValue(false);
    }
    if (!this.form.get('is_location_offer')?.value) {
      this.form.get('is_location_offer')?.setValue(false);
    }

    this.productService.create(this.form.value)
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
          this.reset();
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  private updateProduct() {
    this.productService.update(this.id, this.form.value)
      .pipe(first())
      .subscribe({
        next: () => {
          this.isUpdated = true;
        }, error: (err) => {
          this.error = Object.values(err).join('\r\n');
        }, complete: () => {
          this.hideAlert = false;
        }
      });
  }

  fadeOutMessage(): any {
    setTimeout(() => {
      this.hideAlert = true;
    }, 3000);
  }

  setImage(image: any) {
    this.selectedImage = this.images.find((el: Image) => {
      return el?.id == this.selectedImageId;
    });
  }

  private reset() {
    for (let name in this.form.controls) {
      this.form.controls[name].setValue('');
      this.form.controls[name].setErrors(null);
    }
  }
}
