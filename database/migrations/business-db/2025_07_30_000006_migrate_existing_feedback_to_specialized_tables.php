<?php

declare(strict_types=1);

use App\Models\AudioFeedback;
use App\Models\DesignFeedback;
use App\Models\DocumentFeedback;
use App\Models\GeneralFeedback;
use App\Models\VideoFeedback;
use App\ValueObjects\FeedbackMetadata;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        $this->command->info('Migrating existing feedback data to specialized tables...');

        // Get all existing feedback from the general feedbacks table
        $existingFeedback = DB::connection('business_db')
            ->table('feedbacks')
            ->orderBy('created_at')
            ->get();

        if ($existingFeedback->isEmpty()) {
            $this->command->info('No existing feedback found to migrate.');
            return;
        }

        $migrationStats = [
            'total' => $existingFeedback->count(),
            'video' => 0,
            'audio' => 0,
            'document' => 0,
            'design' => 0,
            'general' => 0,
            'errors' => 0,
        ];

        foreach ($existingFeedback as $feedback) {
            try {
                $metadata = json_decode($feedback->metadata, true);
                
                if (!$metadata || !isset($metadata['type'])) {
                    // No metadata or type - move to general feedback
                    $this->migrateToGeneralFeedback($feedback, null);
                    $migrationStats['general']++;
                    continue;
                }

                $feedbackType = $metadata['type'];
                $migrated = false;

                // Route to appropriate specialized table based on type
                switch ($feedbackType) {
                    case 'video_frame':
                    case 'video_region':
                        $this->migrateToVideoFeedback($feedback, $metadata);
                        $migrationStats['video']++;
                        $migrated = true;
                        break;

                    case 'audio_region':
                        $this->migrateToAudioFeedback($feedback, $metadata);
                        $migrationStats['audio']++;
                        $migrated = true;
                        break;

                    case 'document_block':
                        $this->migrateToDocumentFeedback($feedback, $metadata);
                        $migrationStats['document']++;
                        $migrated = true;
                        break;

                    case 'image_annotation':
                    case 'design_annotation':
                        $this->migrateToDesignFeedback($feedback, $metadata);
                        $migrationStats['design']++;
                        $migrated = true;
                        break;

                    default:
                        // Unknown type - move to general feedback
                        $this->migrateToGeneralFeedback($feedback, $metadata);
                        $migrationStats['general']++;
                        $migrated = true;
                        break;
                }

                if ($migrated) {
                    $this->command->info("Migrated feedback {$feedback->id} ({$feedbackType}) successfully.");
                }

            } catch (\Exception $e) {
                $migrationStats['errors']++;
                Log::error('Failed to migrate feedback', [
                    'feedback_id' => $feedback->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->command->error("Failed to migrate feedback {$feedback->id}: " . $e->getMessage());
            }
        }

        // Display migration statistics
        $this->displayMigrationStats($migrationStats);

        // Optionally rename the old table to preserve data
        if ($migrationStats['errors'] === 0) {
            $this->command->info('Migration completed successfully. Renaming old feedbacks table...');
            DB::connection('business_db')->statement('RENAME TABLE feedbacks TO feedbacks_legacy');
            $this->command->info('✅ Old feedbacks table renamed to feedbacks_legacy');
        } else {
            $this->command->warn('Migration completed with errors. Old feedbacks table preserved.');
        }
    }

    public function down(): void
    {
        $this->command->info('Rolling back feedback migration...');

        // Restore the original feedbacks table if it was renamed
        $tables = DB::connection('business_db')->select("SHOW TABLES LIKE 'feedbacks_legacy'");
        if (!empty($tables)) {
            DB::connection('business_db')->statement('RENAME TABLE feedbacks_legacy TO feedbacks');
            $this->command->info('✅ Restored original feedbacks table');
        }

        // Clear specialized feedback tables
        $this->command->info('Clearing specialized feedback tables...');
        
        DB::connection('business_db')->table('video_feedbacks')->truncate();
        DB::connection('business_db')->table('audio_feedbacks')->truncate();
        DB::connection('business_db')->table('document_feedbacks')->truncate();
        DB::connection('business_db')->table('design_feedbacks')->truncate();
        DB::connection('business_db')->table('general_feedbacks')->truncate();

        $this->command->info('✅ Migration rollback completed');
    }

    private function migrateToVideoFeedback($feedback, array $metadata): void
    {
        $data = $metadata['data'] ?? [];
        $feedbackType = $metadata['type'] === 'video_frame' ? 'frame' : 'region';

        VideoFeedback::create([
            'id' => $feedback->id, // Preserve original ID
            'creator_id' => $feedback->creator_id,
            'content' => $feedback->content,
            'feedbackable_type' => $feedback->feedbackable_type,
            'feedbackable_id' => $feedback->feedbackable_id,
            'status' => $feedback->status,
            'urgency' => $feedback->urgency,
            'resolution' => $feedback->resolution,
            'resolved_by' => $feedback->resolved_by,
            'resolved_at' => $feedback->resolved_at,
            'feedback_type' => $feedbackType,
            'timestamp' => $data['timestamp'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'x_coordinate' => $data['x_coordinate'] ?? null,
            'y_coordinate' => $data['y_coordinate'] ?? null,
            'region_data' => $data['region_data'] ?? null,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
        ]);
    }

    private function migrateToAudioFeedback($feedback, array $metadata): void
    {
        $data = $metadata['data'] ?? [];

        // Ensure we have required time data
        if (empty($data['start_time']) || empty($data['end_time'])) {
            throw new \InvalidArgumentException('Audio feedback requires start_time and end_time');
        }

        AudioFeedback::create([
            'id' => $feedback->id,
            'creator_id' => $feedback->creator_id,
            'content' => $feedback->content,
            'feedbackable_type' => $feedback->feedbackable_type,
            'feedbackable_id' => $feedback->feedbackable_id,
            'status' => $feedback->status,
            'urgency' => $feedback->urgency,
            'resolution' => $feedback->resolution,
            'resolved_by' => $feedback->resolved_by,
            'resolved_at' => $feedback->resolved_at,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'waveform_data' => $data['waveform_data'] ?? null,
            'peak_amplitude' => $data['peak_amplitude'] ?? null,
            'frequency_data' => $data['frequency_data'] ?? null,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
        ]);
    }

    private function migrateToDocumentFeedback($feedback, array $metadata): void
    {
        $data = $metadata['data'] ?? [];

        // Ensure we have required block_id
        if (empty($data['block_id'])) {
            throw new \InvalidArgumentException('Document feedback requires block_id');
        }

        DocumentFeedback::create([
            'id' => $feedback->id,
            'creator_id' => $feedback->creator_id,
            'content' => $feedback->content,
            'feedbackable_type' => $feedback->feedbackable_type,
            'feedbackable_id' => $feedback->feedbackable_id,
            'status' => $feedback->status,
            'urgency' => $feedback->urgency,
            'resolution' => $feedback->resolution,
            'resolved_by' => $feedback->resolved_by,
            'resolved_at' => $feedback->resolved_at,
            'block_id' => $data['block_id'],
            'element_type' => $data['element_type'] ?? null,
            'position_data' => $data['position_data'] ?? null,
            'block_version' => $data['block_version'] ?? null,
            'selection_data' => $data['selection_data'] ?? null,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
        ]);
    }

    private function migrateToDesignFeedback($feedback, array $metadata): void
    {
        $data = $metadata['data'] ?? [];

        // Ensure we have required coordinates
        if (!isset($data['x_coordinate']) || !isset($data['y_coordinate'])) {
            throw new \InvalidArgumentException('Design feedback requires x_coordinate and y_coordinate');
        }

        DesignFeedback::create([
            'id' => $feedback->id,
            'creator_id' => $feedback->creator_id,
            'content' => $feedback->content,
            'feedbackable_type' => $feedback->feedbackable_type,
            'feedbackable_id' => $feedback->feedbackable_id,
            'status' => $feedback->status,
            'urgency' => $feedback->urgency,
            'resolution' => $feedback->resolution,
            'resolved_by' => $feedback->resolved_by,
            'resolved_at' => $feedback->resolved_at,
            'x_coordinate' => $data['x_coordinate'],
            'y_coordinate' => $data['y_coordinate'],
            'annotation_type' => $data['annotation_type'] ?? 'point',
            'annotation_data' => $data['annotation_data'] ?? null,
            'area_bounds' => $data['area_bounds'] ?? null,
            'color' => $data['color'] ?? null,
            'zoom_level' => $data['zoom_level'] ?? null,
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
        ]);
    }

    private function migrateToGeneralFeedback($feedback, ?array $metadata): void
    {
        // Extract category from metadata if available
        $category = null;
        if ($metadata) {
            $category = $metadata['category'] ?? $metadata['type'] ?? null;
            
            // Clean up category to match enum values
            if ($category && !in_array($category, [
                'ui', 'ux', 'content', 'functionality', 'performance', 
                'accessibility', 'security', 'bug', 'feature', 
                'improvement', 'question', 'other'
            ])) {
                $category = 'other';
            }
        }

        GeneralFeedback::create([
            'id' => $feedback->id,
            'creator_id' => $feedback->creator_id,
            'content' => $feedback->content,
            'feedbackable_type' => $feedback->feedbackable_type,
            'feedbackable_id' => $feedback->feedbackable_id,
            'status' => $feedback->status,
            'urgency' => $feedback->urgency,
            'resolution' => $feedback->resolution,
            'resolved_by' => $feedback->resolved_by,
            'resolved_at' => $feedback->resolved_at,
            'metadata' => $metadata,
            'feedback_category' => $category,
            'custom_data' => null, // Can be populated later if needed
            'created_at' => $feedback->created_at,
            'updated_at' => $feedback->updated_at,
        ]);
    }

    private function displayMigrationStats(array $stats): void
    {
        $this->command->info('');
        $this->command->info('📊 Migration Statistics:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("Total feedback processed: {$stats['total']}");
        $this->command->info("├─ Video feedback: {$stats['video']}");
        $this->command->info("├─ Audio feedback: {$stats['audio']}");
        $this->command->info("├─ Document feedback: {$stats['document']}");
        $this->command->info("├─ Design feedback: {$stats['design']}");
        $this->command->info("├─ General feedback: {$stats['general']}");
        $this->command->info("└─ Errors: {$stats['errors']}");
        
        if ($stats['errors'] === 0) {
            $this->command->info('✅ All feedback migrated successfully!');
        } else {
            $this->command->warn("⚠️  {$stats['errors']} errors encountered during migration.");
            $this->command->warn('Check the logs for details.');
        }
    }
};