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
      stock: ['', []],
      price: ['', [Validators.required]],
      brand_id: ['', [Validators.required]],
      category_id: ['', [Validators.required]],
      product_image_id: ['', [Validators.required]],
      is_location_offer: ['', []],
      is_rental: ['', []],
      co2_rating: ['', []]
    });

    // Add conditional validation for stock based on is_rental
    this.form.get('is_rental')?.valueChanges.subscribe(isRental => {
      const stockControl = this.form.get('stock');
      if (isRental) {
        stockControl?.clearValidators();
      } else {
        stockControl?.setValidators([Validators.required]);
      }
      stockControl?.updateValueAndValidity();
    });

    // Set initial stock validation for add mode (non-rental by default)
    if (this.isAddMode) {
      this.form.get('stock')?.setValidators([Validators.required]);
    }

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
              this.form.patchValue({
                id: x.id,
                name: x.name,
                description: x.description,
                stock: x.in_stock,
                price: x.price,
                brand_id: x.brand?.id || x.brand_id,
                category_id: x.category?.id || x.category_id,
                product_image_id: x.product_image?.id || x.product_image_id,
                is_location_offer: x.is_location_offer,
                is_rental: x.is_rental,
                co2_rating: x.co2_rating
              });

              this.selectedImageId = x.product_image?.id || x.product_image_id;
              this.selectedImage = this.images.find((el: Image) => {
                return el?.id == this.selectedImageId;
              });

              // Set initial stock validation based on is_rental value
              const stockControl = this.form.get('stock');
              if (x.is_rental) {
                stockControl?.clearValidators();
              } else {
                stockControl?.setValidators([Validators.required]);
              }
              stockControl?.updateValueAndValidity();
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

    // For rental products, allow stock to be null
    const formValue = {...this.form.value};
    if (formValue.is_rental && !formValue.stock) {
      formValue.stock = null;
    }

    this.productService.create(formValue)
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
    // For rental products, allow stock to be null
    const formValue = {...this.form.value};
    if (formValue.is_rental && !formValue.stock) {
      formValue.stock = null;
    }

    this.productService.update(this.id, formValue)
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
