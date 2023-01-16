import {Invoiceline} from "./invoiceline";

export class Invoice {
  id!: number;
  user_id!: number;
  billing_address!: string;
  billing_city!: string;
  billing_country!: string;
  billing_postcode!: string;
  billing_state!: string;
  invoice_date!: string;
  invoice_number!: string;
  invoicelines!: Invoiceline[];
  payment_account_name!: string;
  payment_account_number!: string;
  payment_method!: string;
  total!: number;
  status!: string;
  status_message!: string;
}
