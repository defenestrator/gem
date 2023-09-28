<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Animal') }}
        </h2>
    </x-slot>

	<div
		x-data="{
			search: '',
			animals: {{$animals}},
			get filteredUsers() {
				return this.animals.filter(
					i => i.name.toLowerCase().startsWith(this.search.toLowerCase())
				)
			}
		}"
	>

	<div class="py-6">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-4 bg-white border-b border-gray-200">
					<input x-model="search" class="mb-2 p-1 border border-gray-400 rounded-md" placeholder="Search...">
					<table class="shadow-lg">
						<thead>
							<tr class="bg-gray-400 text-white font-extrabold" >
							<td class="px-4 py-1 text-center" >ID</td>
							<td class="px-4 py-1">Name</td>
							<td class="px-4 py-1">Email</td>
							</tr>
						</thead>
						<tbody>
							<template x-for="animal in filteredUsers" :key="animal.id">
								<tr>
									<td x-text="animal.id" class="border px-4 py-1 text-center"></td>
									<td x-text="animal.name" class="border px-4 py-1"></td>
									<td x-text="animal.email" class="border px-4 py-1"></td>
								</tr>
							</template>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	</div>
	
</x-app-layout>
