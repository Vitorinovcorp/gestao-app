<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Input
          v-model="search"
          placeholder="Pesquisar..."
          class="max-w-sm"
        />
      </div>
      <Button @click="$emit('create')">
        <Plus class="mr-2 h-4 w-4" />
        Novo
      </Button>
    </div>
    
    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead v-for="col in columns" :key="col.key">
              {{ col.label }}
            </TableHead>
            <TableHead>Ações</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in data" :key="item.id">
            <TableCell v-for="col in columns" :key="col.key">
              <template v-if="col.type === 'image' && item[col.key]">
                <img :src="item[col.key]" class="h-10 w-10 rounded object-cover" />
              </template>
              <template v-else>
                {{ item[col.key] }}
              </template>
            </TableCell>
            <TableCell>
              <div class="flex gap-2">
                <Button variant="ghost" size="sm" @click="$emit('edit', item)">
                  <Edit class="h-4 w-4" />
                </Button>
                <Button variant="ghost" size="sm" @click="$emit('delete', item)">
                  <Trash class="h-4 w-4" />
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
    
    <Pagination
      v-if="pagination"
      :total="pagination.total"
      :per-page="pagination.per_page"
      :current-page="pagination.current_page"
      @page-change="$emit('page-change', $event)"
    />
  </div>
</template>