-- Preview affected rows
SELECT id, common_name
FROM   species
WHERE  common_name ~ '(^|\n)[EGSFJDPCR]:\s'
ORDER  BY id
LIMIT  20;

-- ─── Apply normalizations ─────────────────────────────────────────────────────
-- Flags: g = global (all occurrences), m = multiline (^ matches after newline)
-- Order: abbreviations only — full-language prefixes (Indonesian:, Dutch:, etc.)
-- already present in the data are left untouched.

UPDATE species
SET common_name = regexp_replace(
    regexp_replace(
        regexp_replace(
            regexp_replace(
                regexp_replace(
                    regexp_replace(
                        regexp_replace(
                            regexp_replace(
                                common_name,
                                '^E:\s*', 'English: ', 'gm'   -- E: → English
                            ),
                            '^G:\s*', 'German: ', 'gm'        -- G: → German (Deutsch)
                        ),
                        '^S:\s*', 'Spanish: ', 'gm'           -- S: → Spanish (Español)
                    ),
                    '^F:\s*', 'French: ', 'gm'                -- F: → French (Français)
                ),
                '^J:\s*', 'Japanese: ', 'gm'                  -- J: → Japanese
            ),
            '^D:\s*', 'German: ', 'gm'                        -- D: → German (Deutsch, alt prefix)
        ),
        '^P:\s*', 'Portuguese: ', 'gm'                        -- P: → Portuguese
    ),
    '^C:\s*', 'Chinese: ', 'gm'                               -- C: → Chinese
)
WHERE common_name ~ '(^|\n)[EGSFJDPCe]:\s';
