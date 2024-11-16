# Bela Black's RSS Aggregator

Bela Black's RSS Aggregator is a WordPress plugin that fetches and publishes story posts from various RSS feeds. This plugin is ideal for content curators looking to automate post creation from multiple sources directly into their WordPress website.

## Features

- Fetches posts from multiple RSS feeds
- Option to publish posts as drafts or immediately
- Customizable cron schedule (every 6 hours, 12 hours, or daily)
- Settings page in WordPress Admin for easy configuration
- Manual trigger option to fetch posts immediately
- Easily add, remove, and modify RSS feeds

## Installation

1. **Download** the plugin files and upload them to your WordPress site's `wp-content/plugins` directory.
2. **Activate** the plugin through the Plugins menu in WordPress.
3. **Configure the settings** in WordPress Admin under **Settings > Bela's RSS Aggregator**.

## Configuration

After activation, you can access the plugin's settings under **Settings > Bela's RSS Aggregator** in your WordPress Admin. The settings include:

1. **Max Posts to Fetch** - Set the maximum number of posts to fetch at once.
2. **Post Status** - Choose whether to publish posts immediately or save them as drafts for moderation.
3. **Cron Interval** - Set the automatic fetch interval (every 6 hours, every 12 hours, or daily).
4. **RSS Feed URLs** - Add or edit RSS feed URLs (one URL per line) to specify your content sources.

### Manual Trigger

To fetch posts manually, go to **Settings > Bela's RSS Aggregator** and click the **Manually Run RSS Fetch** button. This triggers the RSS fetch immediately.

## Example Usage

1. **Add RSS Feeds**: Add feed URLs in the **RSS Feed URLs** section.
2. **Choose Post Settings**: Set your desired post limit, post status, and fetch interval.
3. **Run Manually or Wait**: Use the **Manual Trigger** button for an immediate fetch, or wait for the cron job to run based on your selected interval.

## Code Structure

- **ags_fetch_rss_posts()** - The main function that fetches posts from the specified feeds.
- **Settings Page** - The settings page is added under WordPress Admin, allowing users to manage the plugin configuration without editing code.
- **Manual Trigger** - Allows users to trigger an RSS fetch manually for immediate content updates.

## Contributing

Feel free to submit issues and feature requests. Contributions are welcome via pull requests!

## License

This plugin is licensed under the MIT License. See `LICENSE` file for details.

