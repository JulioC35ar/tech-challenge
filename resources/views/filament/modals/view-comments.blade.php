<div class="space-y-4">
    @forelse ($comments as $comment)
        <div class="p-4 border rounded-md shadow-sm">
            <div class="text-sm text-gray-500">
                <strong>{{ $comment->user->name }}</strong>
                <span class="ml-2">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div class="mt-2 text-gray-800">
                {{ $comment->body }}
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">No comments.</p>
    @endforelse

    <hr>

</div>