// export enum Role {
//   Owner = 1,
//   Admin = 2,
//   Writer = 3,
// }

// export const roleLabels: Record<Role, string> = {
//   [Role.Owner]: 'Owner',
//   [Role.Admin]: 'Admin',
//   [Role.Writer]: 'Writer',
// }


export enum Role {
  Owner = 1,
  Admin = 2,
  Pelanggan = 3,
}

export const roleLabels = {
  [Role.Owner]: 'Pimpinan ABC',
  [Role.Admin]: 'Admin Staff',
  [Role.Pelanggan]: 'Pelanggan/Gereja',
};
