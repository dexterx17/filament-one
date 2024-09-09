<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Filament\Resources\PostResource\RelationManagers\AuthorsRelationManager;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Posts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create a Post')
                    ->description('Create posts over here')
                    ->schema([
                        
                        TextInput::make('title')->rules('min:3|max:10')->required(),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),

        
                        Select::make('category_id')
                                ->label('Category')
                                ->relationship('category','name')
                                ->searchable(),
                                // ->options(Category::get()->pluck('name','id')),
                            
                        ColorPicker::make('color')->required(),
                        MarkdownEditor::make('content')->required()->columnSpanFull(),

                    ])->columnSpan(2)
                    ->columns(2),
                    Group::make()->schema([
                        Section::make('Image')->schema([
                            FileUpload::make('thumbnail')->disk('public')->directory('thumbnails'),                            
                        ])
                        ->collapsible(),
                        Section::make('Meta')->schema([
                            TagsInput::make('tags')->required(),
                            Checkbox::make('published')
                        ]),
                        // Section::make('Authors')->schema([
                        //     CheckboxList::make('authors')
                        //         ->label('Co Authors')
                        //         ->relationship('authors', 'name')
                        //         ->searchable()
                        // ])

                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('thumbnail')
                            ->toggleable(),
                TextColumn::make('title')
                            ->description(fn($record) => $record->slug)
                            ->sortable()
                            ->searchable(['title','slug'])
                            ->toggleable(),
                TextColumn::make('category.name')
                    ->toggleable(),
                ColorColumn::make('color')
                    ->toggleable(),
                TextColumn::make('tags')
                    ->toggleable(),
                CheckboxColumn::make('published')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Published on')
                    ->date()
                    ->sortable()
                    ->searchable()
                    ->toggleable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AuthorsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
