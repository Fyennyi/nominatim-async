"""
Geolocation lookup TUI using nominatim-async
Wrapper around the PHP library using subprocess.
Supports both reverse geocoding and text search modes.
"""

import json
import re
import subprocess
import sys
from typing import Optional, Tuple

from textual.app import App, ComposeResult
from textual.containers import Container, Horizontal
from textual.widgets import (
    Header, Footer, Static, Input, Label, Button, RadioButton, RadioSet,
)
from textual.binding import Binding


def parse_dms(text: str) -> Tuple[Optional[float], Optional[float]]:
    """Parse various coordinate formats."""
    lat, lon = None, None

    text = text.strip()

    dec_pattern = r'(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)'
    dec_match = re.search(dec_pattern, text)
    if dec_match:
        lat, lon = float(dec_match.group(1)), float(dec_match.group(2))
        return lat, lon

    dec_pattern2 = r'(-?\d+\.?\d*)\s+(-?\d+\.?\d*)'
    dec_match2 = re.search(dec_pattern2, text)
    if dec_match2:
        lat, lon = float(dec_match2.group(1)), float(dec_match2.group(2))
        if -90 <= lat <= 90 and -180 <= lon <= 180:
            return lat, lon

    dms_patterns: list[tuple[str, str]] = [
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*(?:пн\.?\s*ш\.?|N)', 'lat'),
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*N', 'lat'),
        (r'(\d+)[°](\d+)[′\']\s*(?:пн\.?\s*ш\.?|N)', 'lat'),
        (r'(\d+)[°](\d+)[′\']\s*N', 'lat'),
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*(?:сх\.?\s*д\.?|E)', 'lon'),
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*E', 'lon'),
        (r'(\d+)[°](\d+)[′\']\s*(?:сх\.?\s*д\.?|E)', 'lon'),
        (r'(\d+)[°](\d+)[′\']\s*E', 'lon'),
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]', 'lat'),
        (r'(\d+)[°](\d+)[′\']\s*(?:пн\.?\s*ш\.?)?$', 'lat'),
        (r'(\d+)[°](\d+)[′\'](\d+)[″\"]', 'lon'),
        (r'(\d+)[°](\d+)[′\']\s*(?:сх\.?\s*д\.?)?$', 'lon'),
        (r'(\d+)[°](\d+)[″\"]\s*N', 'lat'),
        (r'(\d+)[°](\d+)[″\"]\s*E', 'lon'),
    ]

    lat_candidates = []
    lon_candidates = []

    wiki_lat_match = re.search(r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*(?:пн\.?\s*ш\.?|N)', text, re.IGNORECASE)
    if wiki_lat_match:
        try:
            deg = float(wiki_lat_match.group(1))
            min = float(wiki_lat_match.group(2))
            sec = float(wiki_lat_match.group(3))
            lat_candidates.append((deg + min / 60 + sec / 3600, wiki_lat_match.start()))
        except (ValueError, IndexError):
            pass

    wiki_lon_match = re.search(r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*(?:сх\.?\s*д\.?|E)', text, re.IGNORECASE)
    if wiki_lon_match:
        try:
            deg = float(wiki_lon_match.group(1))
            min = float(wiki_lon_match.group(2))
            sec = float(wiki_lon_match.group(3))
            lon_candidates.append((deg + min / 60 + sec / 3600, wiki_lon_match.start()))
        except (ValueError, IndexError):
            pass

    wiki_lat_min_match = re.search(r'(\d+)[°](\d+)[′\']\s*(?:пн\.?\s*ш\.?|N)', text, re.IGNORECASE)
    if wiki_lat_min_match:
        try:
            deg = float(wiki_lat_min_match.group(1))
            min = float(wiki_lat_min_match.group(2))
            lat_candidates.append((deg + min / 60, wiki_lat_min_match.start()))
        except (ValueError, IndexError):
            pass

    wiki_lon_min_match = re.search(r'(\d+)[°](\d+)[′\']\s*(?:сх\.?\s*д\.?|E)', text, re.IGNORECASE)
    if wiki_lon_min_match:
        try:
            deg = float(wiki_lon_min_match.group(1))
            min = float(wiki_lon_min_match.group(2))
            lon_candidates.append((deg + min / 60, wiki_lon_min_match.start()))
        except (ValueError, IndexError):
            pass

    ne_lat_match = re.search(r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*N', text, re.IGNORECASE)
    if ne_lat_match:
        try:
            deg = float(ne_lat_match.group(1))
            min = float(ne_lat_match.group(2))
            sec = float(ne_lat_match.group(3))
            lat_candidates.append((deg + min / 60 + sec / 3600, ne_lat_match.start()))
        except (ValueError, IndexError):
            pass

    ne_lon_match = re.search(r'(\d+)[°](\d+)[′\'](\d+)[″\"]\s*E', text, re.IGNORECASE)
    if ne_lon_match:
        try:
            deg = float(ne_lon_match.group(1))
            min = float(ne_lon_match.group(2))
            sec = float(ne_lon_match.group(3))
            lon_candidates.append((deg + min / 60 + sec / 3600, ne_lon_match.start()))
        except (ValueError, IndexError):
            pass

    if not lat_candidates or not lon_candidates:
        simple_dms = re.findall(r'(\d+)[°](\d+)[′\'](\d+)[″\"]', text)
        if len(simple_dms) >= 2:
            for i, (deg, min, sec) in enumerate(simple_dms[:2]):
                val = float(deg) + float(min) / 60 + float(sec) / 3600
                if i == 0:
                    lat_candidates.append((val, 0))
                else:
                    lon_candidates.append((val, 0))
        simple_dms = re.findall(r'(\d+)[°](\d+)[′\'](\d+)[″\"]', text)
        if len(simple_dms) >= 2:
            for i, (deg, min, sec) in enumerate(simple_dms[:2]):
                val = float(deg) + float(min) / 60 + float(sec) / 3600
                if i == 0:
                    lat_candidates.append((val, 0))
                else:
                    lon_candidates.append((val, 0))

    lat_candidates.sort(key=lambda x: x[1])
    lon_candidates.sort(key=lambda x: x[1])

    if lat_candidates:
        lat = lat_candidates[0][0]
    if lon_candidates:
        lon = lon_candidates[0][0]

    if lat is None or lon is None:
        numbers = re.findall(r'-?\d+\.?\d*', text)
        for n in numbers:
            try:
                v = float(n)
                if -90 <= v <= 90 and lat is None:
                    lat = v
                elif -180 <= v <= 180 and lon is None:
                    lon = v
            except ValueError:
                continue

    return lat, lon


class GeolocationApp(App):
    """Main geolocation lookup application."""

    TITLE = "Geolocation Lookup"
    SUBTITLE = "nominatim-async wrapper (reverse + search)"
    BINDINGS = [
        Binding("q,ctrl+c", "quit", "Quit"),
        Binding("enter", "search", "Search"),
        Binding("ctrl+l", "clear", "Clear"),
        Binding("ctrl+r", "toggle_mode", "Toggle Mode"),
    ]

    CSS = """
    Screen {
        layout: vertical;
        background: #1e1e2e;
    }

    #main {
        margin: 1;
    }

    .section-title {
        text-style: bold;
        color: #89b4fa;
        margin-bottom: 1;
    }

    Input {
        width: 1fr;
        margin-bottom: 1;
    }

    .help-text {
        color: #6c7086;
        margin-bottom: 1;
    }

    .hint {
        color: #6c7086;
        margin-bottom: 1;
    }

    #result-content {
        margin-top: 1;
    }

    #status-bar {
        margin-top: 1;
        padding: 1;
        border: solid #45475a;
    }

    .hidden {
        display: none;
    }

    #mode-selector {
        margin-bottom: 1;
    }

    #search-section {
        margin-top: 1;
    }
    """

    def compose(self) -> ComposeResult:
        yield Header(show_clock=True)

        with Container(id="main"):
            yield Static("Search Mode", classes="section-title")

            with RadioSet(id="mode-selector"):
                yield RadioButton("Reverse Geocoding", id="mode-reverse", value=True)
                yield RadioButton("Text Search", id="mode-search", value=False)

            yield Static("", classes="hint")  # Spacer

            # Reverse geocoding section
            with Container(id="reverse-section"):
                yield Static("Find location by coordinates", classes="section-title")

                yield Label("Paste coordinates:", classes="hint")
                yield Input(
                    placeholder="50.3122 28.4314",
                    id="coords-input"
                )

                yield Static("Supported formats:", classes="help-text")
                yield Static(
                    "  Decimal: 50.3122 28.4314\n"
                    "  DMS: 50°18'44\" 28°25'53\"\n"
                    "  Wiki: 50°18′44″ пн. ш. 28°25′53″ сх. д.",
                    classes="help-text"
                )

            # Text search section (initially hidden)
            with Container(id="search-section", classes="hidden"):
                yield Static("Find location by text", classes="section-title")

                yield Label("Enter search query:", classes="hint")
                yield Input(
                    placeholder="Київ, Хрещатик",
                    id="search-input"
                )

                yield Static("Examples:", classes="help-text")
                yield Static(
                    "  City name: Київ\n"
                    "  Address: Хрещатик 1\n"
                    "  Landmark: Майдан Незалежності",
                    classes="help-text"
                )

            yield Static("-" * 50)

            yield Button(" Search ", id="search-btn")

            yield Static("-" * 50)

            yield Static("Result:", classes="section-title")
            yield Static("Select mode and enter search data", id="result-content")

            yield Static("", id="status-bar")

        yield Footer()

    def on_mount(self) -> None:
        self.query_one("#coords-input").focus()
        # Hide search section initially
        self.query_one("#search-section").add_class("hidden")

    def on_radio_set_changed(self, event: RadioSet.Changed) -> None:
        """Handle mode change."""
        radio_set = event.radio_set
        reverse_radio = self.query_one("#mode-reverse", RadioButton)
        search_radio = self.query_one("#mode-search", RadioButton)

        reverse_section = self.query_one("#reverse-section")
        search_section = self.query_one("#search-section")

        if reverse_radio.value:
            reverse_section.remove_class("hidden")
            search_section.add_class("hidden")
            self.query_one("#coords-input").focus()
        else:
            reverse_section.add_class("hidden")
            search_section.remove_class("hidden")
            self.query_one("#search-input").focus()

        self.set_status(f"Mode: {'Reverse Geocoding' if reverse_radio.value else 'Text Search'}", "info")

    def on_input_submitted(self, event: Input.Submitted) -> None:
        """Handle Enter key in input fields."""
        self.search()

    def on_button_pressed(self, event: Button.Pressed) -> None:
        if event.button.id == "search-btn":
            self.search()
            reverse_radio = self.query_one("#mode-reverse", RadioButton)
            if reverse_radio.value:
                self.query_one("#coords-input").focus()
            else:
                self.query_one("#search-input").focus()
    async def action_quit(self) -> None:
        """Quit the application."""
        self.exit()

    def action_search(self) -> None:
        """Trigger search."""
        self.search()

    def action_clear(self) -> None:
        self.query_one("#coords-input", Input).value = ""
        self.query_one("#search-input", Input).value = ""
        self.query_one("#result-content", Static).update("Select mode and enter search data")
        self.query_one("#status-bar", Static).update("")

    def action_toggle_mode(self) -> None:
        """Toggle between search modes."""
        reverse_radio = self.query_one("#mode-reverse", RadioButton)
        search_radio = self.query_one("#mode-search", RadioButton)

        # Switch to the other mode
        if reverse_radio.value:
            search_radio.value = True
        else:
            reverse_radio.value = True

    def search(self) -> None:
        reverse_radio = self.query_one("#mode-reverse", RadioButton)

        if reverse_radio.value:
            self.perform_reverse_search()
        else:
            self.perform_text_search()

    def perform_reverse_search(self) -> None:
        input_widget = self.query_one("#coords-input", Input)
        coords_text = input_widget.value.strip()

        if not coords_text:
            self.set_status("Enter coordinates first", "warning")
            return

        lat, lon = parse_dms(coords_text)

        if lat is None or lon is None:
            self.set_status("Could not parse coordinates", "error")
            self.display_error("Format not recognized")
            return

        if not (-90 <= lat <= 90) or not (-180 <= lon <= 180):
            self.set_status("Coordinates out of range", "error")
            self.display_error(f"Invalid: {lat}, {lon}")
            return

        self.set_status("Reverse geocoding...", "info")
        result = self.call_php_reverse(lat, lon)

        self.process_result(result)

    def perform_text_search(self) -> None:
        input_widget = self.query_one("#search-input", Input)
        query = input_widget.value.strip()

        if not query:
            self.set_status("Enter search query first", "warning")
            return

        self.set_status(f"Searching for '{query}'...", "info")
        result = self.call_php_search(query)

        self.process_result(result)

    def process_result(self, result) -> None:
        if result is None:
            self.set_status("Failed", "error")
            self.display_error("Could not execute lookup")
            return

        if 'error' in result:
            self.set_status("Error", "error")
            self.display_error(result['error'])
        else:
            self.display_result(result)

    def call_php_reverse(self, lat: float, lon: float) -> Optional[dict]:
        try:
            script_dir = __file__.rsplit('/', 2)[0]
            php_script = f"{script_dir}/scripts/geo_lookup.php"

            result = subprocess.run(
                ['php', php_script, 'reverse', str(lat), str(lon)],
                capture_output=True,
                text=True,
                timeout=30
            )

            if result.returncode != 0:
                return {'error': result.stderr}

            return json.loads(result.stdout)

        except Exception as e:
            return {'error': str(e)}

    def call_php_search(self, query: str) -> Optional[dict]:
        try:
            script_dir = __file__.rsplit('/', 2)[0]
            php_script = f"{script_dir}/scripts/geo_lookup.php"

            result = subprocess.run(
                ['php', php_script, 'search', query],
                capture_output=True,
                text=True,
                timeout=30
            )

            if result.returncode != 0:
                return {'error': result.stderr}

            return json.loads(result.stdout)

        except Exception as e:
            return {'error': str(e)}

    def set_status(self, message: str, status_type: str = "info") -> None:
        status = self.query_one("#status-bar", Static)
        icons = {"info": "•", "warning": "!", "error": "✗", "success": "✓"}
        icon = icons.get(status_type, "•")
        status.update(f"{icon} {message}")

    def display_result(self, result: dict) -> None:
        content = self.query_one("#result-content", Static)

        matched_by = result.get('matched_by', 'unknown')
        similarity = result.get('similarity')
        metadata = result.get('metadata', {})
        search_mode = metadata.get('search_mode', 'unknown')

        match_str = matched_by
        if similarity:
            match_str += f" ({similarity*100:.0f}%)"

        osm_id = result.get('osm_id', 'unknown')
        osm_type = result.get('osm_type', 'unknown')

        lines = [
            f"[green]{result['name']}[/green]",
            f"UID: {osm_id} | OSM Type: {osm_type} | Type: {result['type']}",
        ]

        # Add additional fields if available
        if result.get('category'):
            lines.append(f"Category: {result['category']}")

        if result.get('oblast_name'):
            lines.append(f"Oblast: {result['oblast_name']}")

        if result.get('district_name'):
            lines.append(f"District: {result['district_name']}")

        if result.get('city_name'):
            lines.append(f"City: {result['city_name']}")

        if result.get('country'):
            lines.append(f"Country: {result['country']}")

        if result.get('postcode'):
            lines.append(f"Postcode: {result['postcode']}")

        lines.extend([
            f"[dim]Coords: {result['coordinates']['lat']}, {result['coordinates']['lon']}[/dim]",
            f"[dim]Search: {search_mode} | Match: {match_str}[/dim]",
        ])

        if result.get('importance'):
            lines.append(f"[dim]Importance: {result['importance']}[/dim]")

        content.update("\n".join(lines))
        self.set_status("Found!", "success")

    def display_error(self, message: str) -> None:
        content = self.query_one("#result-content", Static)
        content.update(f"[red]Error: {message}[/red]")


if __name__ == "__main__":
    app = GeolocationApp()
    app.run()
