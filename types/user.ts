export interface User {
  id: string
  nome: string
  email: string
  telefone: string
  grupoPermissoes: string
  estado: 'ativo' | 'inativo'
  is_active?: boolean  
}

export interface UserFormData {
  nome: string
  email: string
  telefone: string
  grupoPermissoes: string
  estado: 'ativo' | 'inativo'
}

export const gruposPermissoes = [
  { value: 'admin', label: 'Administrador' },
  { value: 'gestor', label: 'Gestor' },
  { value: 'operador', label: 'Operador' },
  { value: 'visualizador', label: 'Visualizador' }
]