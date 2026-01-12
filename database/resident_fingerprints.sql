-- Table structure for table `resident_fingerprints`
--

CREATE TABLE `resident_fingerprints` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `template` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `resident_fingerprints`
--

ALTER TABLE `resident_fingerprints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- AUTO_INCREMENT for table `resident_fingerprints`
--

ALTER TABLE `resident_fingerprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `resident_fingerprints`
--

ALTER TABLE `resident_fingerprints`
  ADD CONSTRAINT `resident_fingerprints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;
