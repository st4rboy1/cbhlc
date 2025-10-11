import { useState } from "react"

type User = {
  id: number
  name: string
  email: string
  role: string
}

type UsersTableProps = {
  users: User[]
}

export function UsersTable({ users }: UsersTableProps) {
  const [sortKey, setSortKey] = useState<keyof User>("id")
  const [sortOrder, setSortOrder] = useState<"asc" | "desc">("asc")

  const handleSort = (key: keyof User) => {
    if (sortKey === key) {
      setSortOrder(sortOrder === "asc" ? "desc" : "asc")
    } else {
      setSortKey(key)
      setSortOrder("asc")
    }
  }

  const sortedUsers = [...users].sort((a, b) => {
    const aValue = a[sortKey]
    const bValue = b[sortKey]

    if (aValue < bValue) return sortOrder === "asc" ? -1 : 1
    if (aValue > bValue) return sortOrder === "asc" ? 1 : -1
    return 0
  })

  return (
    <div className="border border-border rounded-lg overflow-hidden">
      <table className="w-full">
        <thead>
          <tr className="border-b border-border bg-muted/50">
            <th
              className="text-left px-4 py-3 text-sm font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors"
              onClick={() => handleSort("id")}
            >
              ID {sortKey === "id" && (sortOrder === "asc" ? "↑" : "↓")}
            </th>
            <th
              className="text-left px-4 py-3 text-sm font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors"
              onClick={() => handleSort("name")}
            >
              Name {sortKey === "name" && (sortOrder === "asc" ? "↑" : "↓")}
            </th>
            <th
              className="text-left px-4 py-3 text-sm font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors"
              onClick={() => handleSort("email")}
            >
              Email {sortKey === "email" && (sortOrder === "asc" ? "↑" : "↓")}
            </th>
            <th
              className="text-left px-4 py-3 text-sm font-medium text-muted-foreground cursor-pointer hover:text-foreground transition-colors"
              onClick={() => handleSort("role")}
            >
              Role {sortKey === "role" && (sortOrder === "asc" ? "↑" : "↓")}
            </th>
          </tr>
        </thead>
        <tbody>
          {sortedUsers.map((user, index) => (
            <tr
              key={user.id}
              className={`border-b border-border last:border-0 hover:bg-muted/30 transition-colors ${
                index % 2 === 0 ? "bg-background" : "bg-muted/10"
              }`}
            >
              <td className="px-4 py-3 text-sm text-foreground">{user.id}</td>
              <td className="px-4 py-3 text-sm text-foreground font-medium">{user.name}</td>
              <td className="px-4 py-3 text-sm text-muted-foreground">{user.email}</td>
              <td className="px-4 py-3">
                <span
                  className={`inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${
                    user.role === "registrar"
                      ? "bg-chart-3/10 text-chart-3"
                      : user.role === "guardian"
                        ? "bg-chart-4/10 text-chart-4"
                        : "bg-muted text-muted-foreground"
                  }`}
                >
                  {user.role}
                </span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}