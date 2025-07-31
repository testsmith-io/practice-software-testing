import {Invoiceline} from "./invoiceline";

export interface Invoice {
  id?: string;
  user_id?: string;
  billing_street?: string;
  billing_city?: string;
  billing_country?: string;
  billing_postal_code?: string;
  billing_state?: string;
  invoice_date?: string;
  invoice_number?: string;
  additional_discount_percentage?: number;
  additional_discount_amount?: number;
  invoicelines?: Invoiceline[];
  payment?: any;
  subtotal?: number;
  total?: number;
  status?: string;
  status_message?: string;
  created_at: string;
}
