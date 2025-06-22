import {Address} from "./address";

export class User {
  id?: number;
  first_name?: string;
  last_name?: string;
  dob?: string;
  address?: Address;
  phone?: boolean;
  email: boolean;
  password?: boolean;
}
