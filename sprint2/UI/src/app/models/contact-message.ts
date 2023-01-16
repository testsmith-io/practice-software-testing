export class ContactMessage {
  id?: number;
  name?: string;
  email?: string;
  subject?: string;
  message!: string;
  created_at?: string;
  status?: string;
}
