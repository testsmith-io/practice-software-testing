import {Invoiceline} from "./invoiceline";

export class Invoice {
  id!: number;
  user_id!: number;
  billing_street!: string;
  billing_city!: string;
  billing_country!: string;
  billing_postal_code!: string;
  billing_state!: string;
  invoice_date!: string;
  invoice_number!: string;
  additional_discount_percentage!: number;
  additional_discount_amount!: number;
  invoicelines!: Invoiceline[];
  payment!: any;
  payment_account_name!: string;
  payment_account_number!: string;
  payment_method!: string;
  subtotal!: number;
  total!: number;
  status!: string;
  status_message!: string;
}
